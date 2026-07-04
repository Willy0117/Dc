<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationDocument;
use App\Models\Organization;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CloudSignService
{
    private string $baseUrl;
    private string $clientId;
    private ?string $accessToken = null;

    public function __construct(
        private PdfService $pdfService,
        private FileService $fileService
    ) {
        $this->baseUrl  = config('services.cloudsign.api_url');
        $this->clientId = config('services.cloudsign.client_id');
    }

    // -------------------------
    // アクセストークン取得
    // -------------------------

    /**
     * クライアントIDからアクセストークンを取得する（1時間有効）
     */
    private function getAccessToken(): string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $response = Http::asForm()->post("{$this->baseUrl}/token", [
            'client_id' => $this->clientId,
        ]);

        if ($response->failed()) {
            Log::error('CloudSign: アクセストークン取得失敗', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('CloudSign: アクセストークン取得に失敗しました。');
        }

        $this->accessToken = $response->json('access_token');

        return $this->accessToken;
    }

    // -------------------------
    // sign()から呼ばれるメインメソッド
    // -------------------------

    /**
     * DB登録 → クラウドサイン送信（PDF生成済みのパスを受け取る）
     */
    public function send(Organization $organization, array $data): void
    {
        $pdfPath = $data['pdf_path'];

        // 1. applications に登録
        $application = Application::create([
            'organization_id' => $organization->id,
            'status'          => 0, // 申込中
        ]);

        // 2. application_documents に登録（契約書）
        ApplicationDocument::create([
            'application_id' => $application->id,
            'type'           => ApplicationDocument::TYPE_CONTRACT,
            'file_path'      => $pdfPath,
        ]);

        // 合意書がある場合も登録
        if (!empty($data['agreement_pdf_path'])) {
            ApplicationDocument::create([
                'application_id' => $application->id,
                'type'           => ApplicationDocument::TYPE_AGREEMENT,
                'file_path'      => $data['agreement_pdf_path'],
            ]);
        }

        // 3. クラウドサインへ送信
        $title = !empty($data['needs_agreement'])
            ? '動注治療ライセンス契約書・合意書'
            : '動注治療ライセンス契約書';

        $documentId = $this->sendContract(
            pdfPath:          $this->fileService->getLocalTempPath($pdfPath),
            agreementPdfPath: !empty($data['agreement_pdf_path'])
                ? $this->fileService->getLocalTempPath($data['agreement_pdf_path'])
                : null,
                
            title:        $title,
            note:         'ライセンス契約書をお送りします。内容をご確認のうえ、電子署名をお願いいたします。',
            participants: [
                [
                    'email'        => $data['email'],
                    'name'         => $data['rep_last_name'] . $data['rep_first_name'],
                    'organization' => $data['corp_name'],
                ],
            ]
        );

        // 4. cloudsign_document_id・ステータスを更新
        $application->update([
            'cloudsign_document_id' => $documentId,
            'status'                => 1, // 送信済
        ]);

        Log::info('CloudSign: 送信完了', [
            'organization_id' => $organization->id,
            'application_id'  => $application->id,
            'document_id'     => $documentId,
        ]);
    }

    // -------------------------
    // 書類作成
    // -------------------------

    public function createDocument(string $title, string $note = ''): array
    {
        $response = $this->post('/documents', [
            'document' => [
                'title' => $title,
                'note'  => $note,
            ],
        ]);

        return [
            'document_id' => $response->json('id'),
        ];
    }

    // -------------------------
    // ファイルアップロード
    // -------------------------

    public function uploadFile(string $documentId, string $pdfPath, string $displayName = '契約書.pdf'): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->attach('uploadfile', file_get_contents($pdfPath), $displayName, ['Content-Type' => 'application/pdf'])
            ->post("{$this->baseUrl}/documents/{$documentId}/files", [
                'name' => $displayName,
            ]);

        $this->handleError($response);

        return [
            'file_id' => $response->json('id'),
        ];
    }

    // -------------------------
    // 宛先（参加者）設定
    // -------------------------

    public function addParticipants(string $documentId, array $participants): void
    {
        foreach ($participants as $order => $participant) {
            $this->post("/documents/{$documentId}/participants", [
                'email'        => $participant['email'],
                'name'         => $participant['name'],
                'organization' => $participant['organization'] ?? '',
                'order'        => $order + 1,
            ]);
        }
    }

    // -------------------------
    // 書類送信
    // -------------------------

    public function sendDocument(string $documentId): void
    {
        $this->post("/documents/{$documentId}", []);
    }

    // -------------------------
    // 書類取得
    // -------------------------

    public function getDocument(string $documentId): array
    {
        $response = $this->get("/documents/{$documentId}");

        return $response->json();
    }

    // -------------------------
    // 締結済みファイルのダウンロード
    // -------------------------

    /**
     * 締結済み書類の特定ファイルをダウンロードして保存する
     *
     * @param  string $documentId
     * @param  string $fileId
     * @param  int    $index  0=契約書, 1=合意書
     * @return string 保存した相対パス
     */
    public function downloadSignedFileById(string $documentId, string $fileId, int $index = 0): string
    {
        $response = Http::withToken($this->getAccessToken())
            ->withOptions(['decode_content' => false])
            ->get("{$this->baseUrl}/documents/{$documentId}/files/{$fileId}");

        $this->handleError($response);

        $suffix   = $index === 0 ? 'contract' : 'agreement';
        $fileName = "contracts/signed_{$suffix}_{$documentId}.pdf";

        Storage::disk(config('filesystems.default'))->put($fileName, $response->body());

        return $fileName;
    }

    /**
     * 締結済み書類のPDFファイルをダウンロードして保存する（1ファイル目のみ・後方互換用）
     */
    public function downloadSignedFile(string $documentId): string
    {
        $document = $this->getDocument($documentId);
        $fileId   = $document['files'][0]['id'] ?? null;

        if (!$fileId) {
            throw new \RuntimeException('CloudSign: 締結済みファイルのfileIDが取得できませんでした。');
        }

        return $this->downloadSignedFileById($documentId, $fileId, 0);
    }

    // -------------------------
    // 一括フロー
    // -------------------------

    public function sendContract(
        string $pdfPath,
        ?string $agreementPdfPath,
        string $title,
        string $note,
        array $participants
    ): string {
        ['document_id' => $documentId] = $this->createDocument($title, $note);
        Log::info('CloudSign: 書類作成完了', ['document_id' => $documentId]);

        // 契約書アップロード
        $this->uploadFile($documentId, $pdfPath, '契約書.pdf');
        @unlink($pdfPath); //
        Log::info('CloudSign: 契約書PDFアップロード完了', ['document_id' => $documentId]);

        // 合意書アップロード（再契約の場合のみ）
        if ($agreementPdfPath) {
            $this->uploadFile($documentId, $agreementPdfPath, '合意書.pdf');
            @unlink($agreementPdfPath);
            Log::info('CloudSign: 合意書PDFアップロード完了', ['document_id' => $documentId]);
        }

        $this->addParticipants($documentId, $participants);
        Log::info('CloudSign: 宛先設定完了', ['document_id' => $documentId]);

        $this->sendDocument($documentId);
        Log::info('CloudSign: 書類送信完了', ['document_id' => $documentId]);

        return $documentId;
    }

    // -------------------------
    // 内部ヘルパー
    // -------------------------

    private function post(string $path, array $data): Response
    {
        $response = Http::asForm()
            ->withToken($this->getAccessToken())
            ->post("{$this->baseUrl}{$path}", $data);

        $this->handleError($response);

        return $response;
    }

    private function get(string $path): Response
    {
        $response = Http::withToken($this->getAccessToken())
            ->get("{$this->baseUrl}{$path}");

        $this->handleError($response);

        return $response;
    }

    private function handleError(Response $response): void
    {
        if ($response->failed()) {
            Log::error('CloudSign API エラー', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException(
                'CloudSign API エラー: ' . $response->status() . ' ' . $response->body()
            );
        }
    }
}