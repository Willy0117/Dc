<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\OrganizationContract;
use App\Models\WebhookLog;
use App\Services\CloudSignService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CloudSignWebhookController extends Controller
{
    public function __construct(
        private CloudSignService $cloudSign
    ) {}

    // クラウドサインのWebhook送信元IPアドレス
    private const ALLOWED_IPS = [
        // 本番環境
        '52.68.17.229',
        '52.198.144.82',
        '3.112.114.42',
        // サンドボックス環境
        '52.197.119.179',
    ];

    // 変更点：クラウドサイン公式ドキュメント記載のstatus値の意味。
    // https://help.cloudsign.jp/ja/articles/9977727
    // WebhookLog.event_typeに、機械的な"status_2"ではなく
    // 人が読んで分かるラベルで保存するために使う。
    private const STATUS_LABELS = [
        1 => '先方確認中',
        2 => '締結完了',
        3 => '取り消し・却下',
    ];

    public function handle(Request $request): Response
    {
        // IP制限
        if (!in_array($request->ip(), self::ALLOWED_IPS)) {
            Log::warning('CloudSign Webhook: 不正なIPからのアクセス', [
                'ip' => $request->ip(),
            ]);
            return response('Forbidden', 403);
        }

        $documentId = $request->input('documentID');
        $status     = $request->input('status');

        Log::info('CloudSign Webhook 受信', [
            'documentID' => $documentId,
            'status'     => $status,
        ]);

        // 受信内容を記録（管理画面の通知表示用）
        WebhookLog::create([
            'source'     => WebhookLog::SOURCE_CLOUDSIGN,
            // 変更点：'status_2' ではなく '締結完了' のように、
            // 管理画面でそのまま表示しても分かるラベルを保存する。
            // 未知のstatus値が来た場合のフォールバックも残しておく。
            'event_type' => self::STATUS_LABELS[(int) $status] ?? ('status_' . $status),
            'payload'    => json_encode($request->all()),
            'created_at' => now(),
        ]);

        if (!$documentId || !in_array($status, [1, 2, 3])) {
            return response('Bad Request', 400);
        }

        $application = Application::where('cloudsign_document_id', $documentId)->first();

        if (!$application) {
            Log::error('CloudSign Webhook: 対応するapplicationが見つかりません', [
                'documentID' => $documentId,
            ]);
            // 404を返すと再送されないよう200を返す
            return response('OK', 200);
        }

        // statusが1（先方確認中）の通知は記録のみ行いステータス更新はしない
        if ($status === 1) {
            return response('OK', 200);
        }

        $application->update(['status' => $status]);

        Log::info('CloudSign Webhook: ステータス更新完了', [
            'application_id' => $application->id,
            'status'         => $status,
        ]);

        // 締結完了時は最終版PDFを取得して保存する
        if ((int)$status === 2) {
            $this->saveSignedDocument($application, $documentId);
            // $this->reconcileContractStartedAt($application);

            // 契約日を更新（license_issued_atも更新）
            $application->organization->updateContractDate(true);
        }

        return response('OK', 200);
    }

    /**
     * クラウドサイン締結完了日と、入金確認で確定済みのstarted_atを比較し、
     * 遅い方（より後の日付）をcontractのstarted_atとして採用する。
     * まだ入金確認が済んでいない（started_atがnull）場合は、署名完了日をセットする。
     */
    private function reconcileContractStartedAt(Application $application): void
    {
        $contract = OrganizationContract::where('organization_id', $application->organization_id)
            ->orderByDesc('created_at')
            ->first();

        if (!$contract) {
            Log::warning('CloudSign Webhook: 対応するorganization_contractsが見つかりません', [
                'organization_id' => $application->organization_id,
            ]);
            return;
        }

        $signedDate = now()->toDateString();

        if (!$contract->started_at) {
            // まだ入金確認が済んでいない → 署名完了日を仮の契約開始日とする
            $contract->update(['started_at' => $signedDate]);

            Log::info('CloudSign Webhook: 契約開始日を署名完了日で確定しました', [
                'organization_contract_id' => $contract->id,
                'started_at'               => $signedDate,
            ]);
            return;
        }

        // 既に入金確認で確定済み → 署名完了日の方が後ろであれば上書きする
        if ($signedDate > $contract->started_at->toDateString()) {
            $contract->update(['started_at' => $signedDate]);

            Log::info('CloudSign Webhook: 契約開始日を署名完了日に更新しました（署名完了の方が遅いため）', [
                'organization_contract_id' => $contract->id,
                'old_started_at'           => $contract->started_at,
                'new_started_at'           => $signedDate,
            ]);
        }
    }

    /**
     * 締結済みPDFをクラウドサインから取得し、application_documentsに保存する
     * 複数ファイル（契約書・合意書）に対応
     */
    private function saveSignedDocument(Application $application, string $documentId): void
    {
        try {
            $document = $this->cloudSign->getDocument($documentId);
            $files    = $document['files'] ?? [];

            foreach ($files as $index => $file) {
                $fileId  = $file['id'] ?? null;
                if (!$fileId) continue;

                $pdfPath = $this->cloudSign->downloadSignedFileById($documentId, $fileId, $index);

                // 1ファイル目は契約書、2ファイル目は合意書として保存
                $type = $index === 0
                    ? ApplicationDocument::TYPE_SIGNED
                    : ApplicationDocument::TYPE_AGREEMENT_SIGNED;

                ApplicationDocument::create([
                    'application_id' => $application->id,
                    'type'           => $type,
                    'file_path'      => $pdfPath,
                ]);

                Log::info('CloudSign Webhook: 締結済みPDF保存完了', [
                    'application_id' => $application->id,
                    'type'           => $type,
                    'pdf_path'       => $pdfPath,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('CloudSign Webhook: 締結済みPDF取得に失敗しました', [
                'application_id' => $application->id,
                'documentID'     => $documentId,
                'message'        => $e->getMessage(),
            ]);
        }
    }
}