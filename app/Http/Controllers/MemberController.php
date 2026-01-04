<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use TCPDF_FONTS;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\Member;
use App\Models\PreUser;
use Imagick;

class MemberController extends Controller
{
    // 1. 誓約 + 加盟団体 ページ
    public function showRegistrationForm($token)
    {
        return Inertia::render('Members/AgreeAndAffiliates', [
            'token' => $token,
        ]);
    }

    // 2. 誓約チェック後、Registerへ遷移
    public function agreeNext(Request $request, $token)
    {
        // バリデーション
        $request->validate([
            'agree' => 'required|boolean',
        ]);

        // セッション保存
        session([
            'agree' => $request->agree,
            'affiliate' => $request->affiliate,
            'agree_at'  => now()->toDateTimeString(), // 追加
        ]);

        return redirect()->route('members.register.register', ['token' => $token]);
    }

    // 3. Register 入力ページ
    public function showRegisterForm($token)
    {
        $form = session('member_form', [
            'agree'     => session('agree', false),
            'affiliate' => session('affiliate', null),
            'agree_at'  => session('agree_at', null),
        ]);        
        return Inertia::render('Members/Register', [
            'token' => $token,
            'form'  => $form,
        ]);
    }

    // 4. 完了処理（PDF2点）
    public function completeRegistration(Request $request, string $token)
    {
        // 仮登録ユーザー取得（email 用）
        $preUser = PreUser::where('token', $token)->firstOrFail();

        $validated = $request->validate([
            // member
            'company_name' => 'required|string',
            'company_furigana' => 'required|string',
            'representative' => 'required|string',
            'representative_furigana' => 'required|string',
            'address_zip' => 'required|string',
            'address' => 'required|string',
            'tel' => 'required|string',
            'fax' => 'nullable|string',
            'mobile' => 'nullable|string',
            'staff' => 'nullable|string',

            // bank
            'bank_type' => 'required|integer',
            'bank_name' => 'required|string',
            'bank_code' => 'nullable|string',
            'branch_name' => 'required|string',
            'branch_code' => 'nullable|string',
            'account_type' => 'required|string',
            'account_no' => 'required|string',
            'account_kana' => 'required|string',
            'account_name' => 'required|string',

            // pdf
            'history_certificate' => 'required|file|mimes:pdf',
        ]);

        $member = null;

        try {
            DB::transaction(function () use ($validated, $request, $preUser, &$member) {

                // PDF保存（public）
                $pdfRelativePath = $request->file('history_certificate')
                    ->store('members/history_certificates', 'public');

                $pdfFullPath = storage_path('app/public/' . $pdfRelativePath);

                // サムネイル保存先
                $thumbnailRelativePath =
                    'members/history_certificates/thumbnails/' . basename($pdfRelativePath, '.pdf') . '.png';

                $thumbnailFullPath = storage_path('app/public/' . $thumbnailRelativePath);

                if (!Storage::disk('public')->exists('members/history_certificates/thumbnails')) {
                    Storage::disk('public')->makeDirectory('members/history_certificates/thumbnails');
                }

                // thumbnail 生成
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                $imagick->readImage($pdfFullPath . '[0]');
                $imagick->setImageFormat('png');
                $imagick->writeImage($thumbnailFullPath);
                $imagick->clear();
                $imagick->destroy();

                // members
                $member = Member::create([
                    'company_name' => $validated['company_name'],
                    'company_furigana' => $validated['company_furigana'],
                    'representative' => $validated['representative'],
                    'representative_furigana' => $validated['representative_furigana'],
                    'address_zip' => $validated['address_zip'],
                    'address' => $validated['address'],
                    'email' => $preUser->email,
                    'tel' => $validated['tel'],
                    'fax' => $validated['fax'] ?? null,
                    'mobile' => $validated['mobile'] ?? '',
                    'staff' => $validated['staff'] ?? '',
                    'agree' => 1,
                    'affiliate' => 1,
                    'agreed_at' => now(),
                    'history_certificate_path' => $pdfRelativePath,
                    'history_certificate_thumbnail_path' => $thumbnailRelativePath,
                    'status' => 1,
                ]);

                // bank_accounts
                $member->bankAccount()->create([
                    'bank_type' => $validated['bank_type'],
                    'bank_name' => $validated['bank_name'],
                    'bank_code' => $validated['bank_code'] ?? null,
                    'branch_name' => $validated['branch_name'],
                    'branch_code' => $validated['branch_code'] ?? null,
                    'account_type' => $validated['account_type'],
                    'account_no' => $validated['account_no'],
                    'account_kana' => $validated['account_kana'],
                    'account_name' => $validated['account_name'],
                ]);

                session()->forget(['agree', 'affiliate', 'agree_at']);

                $preUser->update([
                    'verified_at' => now(),
                ]);
            });
        } catch (\Exception $e) {
            throw $e;
            //return back()->withErrors(['error' => '登録処理に失敗しました: ' . $e->getMessage()]);
        }            

        // 成功時のみ member_id を渡す
        if (!$member) {
            return back()->withErrors(['error' => '登録に失敗しました。']);
        }
        return redirect()->route('members.complete')
            ->with('success', 'ご登録ありがとうございました');
/*
        return redirect()->route('members.complete')
            ->with('member_id', $member->id)
            ->with('success', 'ご登録ありがとうございました');
*/
    }


    private function generatePdfThumbnail(string $pdfPath): string
    {
        $pdfFullPath = Storage::disk('public')->path($pdfPath);

        $imagick = new Imagick();
        $imagick->setResolution(150, 150);
        $imagick->readImage($pdfFullPath . '[0]'); // 1ページ目
        $imagick->setImageFormat('jpg');
        $imagick->thumbnailImage(300, 0);

        $thumbnailName = pathinfo($pdfPath, PATHINFO_FILENAME) . '.jpg';
        $thumbnailPath = 'certificates/thumbnail/' . $thumbnailName;

        Storage::disk('public')->put(
            $thumbnailPath,
            $imagick->getImageBlob()
        );

        $imagick->clear();
        $imagick->destroy();

        return $thumbnailPath;
    }
 
    // Apuls Pdf Create
    public function pdfCreate()
    {
        $form = session('member_form', [
            'company_furigana' => 'クーネット',
            'representative_furigana' => '',
            'company_name' => '',
            'representative' => '',
            'address_zip' => '',
            'address' => '',
            'tel' => '',
            'bank_name' => '',
            'branch_name' => '',
            'account_type' => '普通',
            'account_no' => '',
            'account_kana' => '',
            'account_name' => '',
        ]);

        return Inertia::render('Members/PdfCreate', [
            'form' => $form
        ]);
    }
    // Apuls Pdf Generate
    public function pdfGenerate(Request $request)
    {
        $data = $request->validate([
            'company_furigana'=> 'required|string',
            'representative_furigana'=> 'required|string',
            'company_name'=> 'required|string',
            'representative'=> 'required|string',
            'address_zip'=> 'required|string',
            'address'=> 'required|string',
            'tel'=> 'required|string',
            'bank_type'    => 'required|integer',
            'bank_name'    => 'required|string',
            'branch_name'  => 'required|string',
            'account_type' => 'required|string',
            'account_no'   => 'required|string',
            'account_kana'   => 'required|string',
            'account_name' => 'required|string',
        ]);

        // フォーム全体を取得
        $form = $request->all();

        // agree情報もまとめて保存
        $form['agree']     = session('agree', false);
        $form['affiliate'] = session('affiliate', null);
        $form['agree_at']  = session('agree_at', null);

        // session に保存
        session(['member_form' => $form]);


                // FPDI + TCPDF
        $pdf = new Fpdi();
        // ページ追加
        $pdf->AddPage();

        // 既存PDFテンプレート読み込み
        $templatePath = storage_path('app/templates/aplus.pdf');
        $pageCount = $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);
        //$pdf->useTemplate($tpl, 0, 0, 0, 0, true);


        // TCPDF同梱の日本語フォント
        $pdf->AddFont('kozminproregular', '', 'kozminproregular.php', true);
        $pdf->SetFont('kozminproregular', '', 12);

        // ---- 1) 契約者名（フリガナ）
        $pdf->SetXY(50, 65);
        $pdf->Write(8, $data['company_furigana']);

        // ---- 2) 契約者名（漢字）
        $pdf->SetXY(50, 80);
        $pdf->Write(8, $data['company_name']);

        // ---- 3) zip code
        $pdf->SetXY(50, 85);
        $pdf->Write(8, $data['address_zip']);

        // ---- 3) 住所
        $pdf->SetXY(50, 95);
        $pdf->Write(8, $data['address']);

        // ---- 4) 電話番号
        $pdf->SetXY(140, 100);
        $pdf->Write(8, $data['tel']);

        // ---- 5) 銀行名
        $pdf->SetXY(105, 145);
        $pdf->Write(8, $data['bank_name']);

        // ---- 6) 支店名
        $pdf->SetXY(150, 145);
        $pdf->Write(8, $data['branch_name']);

        // ---- 7) 預金種目（普通 / 当座 → マル）
        if ($data['account_type'] === '普通') {
            $pdf->SetXY(103, 160);
        } else {
            $pdf->SetXY(128, 160);
        }
        $pdf->Write(8, '〇');

        // ---- 8) 口座番号（記号）
        $pdf->SetXY(150, 162);
        $pdf->Write(8, $data['account_no']);

        // ---- 9) 口座名義（フリガナ）
        $pdf->SetXY(35, 170);
        $pdf->Write(8, $data['account_kana']);

        // ---- 10) 口座名義（漢字）
        $pdf->SetXY(35, 190);
        $pdf->Write(8, $data['account_name']);

        // 保存先ファイル名
        $output = 'generated/bank-info-' . time() . '.pdf';
        $file_path = storage_path('app/public/' . $output);

        // ディレクトリが存在しない場合は作成
        if (!file_exists(dirname($file_path))) {
            mkdir(dirname($file_path), 0775, true);
        }

        // PDFを直接ファイルに書き込む
        $pdf->Output($file_path, 'F');

        // JSONでURL返却
        return response()->json([
            'url' => Storage::url($output)
        ]);    
    }

    public function pdfPreview(Request $request,$token)
    {
        return Inertia::render('Members/PdfPreview', [
            'token'  => $token,
            'pdfUrl' => $request->query('pdfUrl'),
        ]);
    }

    public function showRejectedMessage($token)
    {
        return Inertia::render('Members/Rejected', [
            'token' => $token,
            'message' => '大変申し訳ありませんが、当団体への加盟はお受け出来かねます。',
        ]);
    }

    public function bank()
    {
        return Inertia::render('Members/Bank');
    }
    
    public function pdf()
    {
        $data = [
            'company_furigana'    => 'クーネット',
            'company_name'    => '株式会社クーネット',
            'address_zip'=> '224-0021',
            'address'    => '横浜市都筑区北山田２丁目３番３号',   
            'tel'    => '０４５−５９０−００９０',   
            'bank_name'    => '横浜',
            'branch_name'  => 'センター',
            'account_type' => '当座',
            'account_no'   => '１２３４５６７',
            'account_kana'   => 'クーネット',
            'account_name' => '株式会社クーネット　代表取締役　雲田敏広',
        ];
            // FPDI + TCPDF
    $pdf = new Fpdi();

    // ページ追加
    $pdf->AddPage();

    // 既存PDFテンプレート読み込み
    $templatePath = storage_path('app/templates/aplus.pdf');
    $pageCount = $pdf->setSourceFile($templatePath);
    $tpl = $pdf->importPage(1);
    $pdf->useTemplate($tpl);


    // TCPDF同梱の日本語フォント
    $pdf->SetFont('kozminproregular', '', 12); // もしくは cid0jp

// ---- 1) 契約者名（フリガナ）
$pdf->SetXY(50, 65);
$pdf->Write(8, $data['company_furigana']);

// ---- 2) 契約者名（漢字）
$pdf->SetXY(50, 75);
$pdf->Write(8, $data['company_name']);
        // ---- 3) zip code
        $pdf->SetXY(50, 85);
        $pdf->Write(8, $data['address_zip']);

// ---- 3) 住所
$pdf->SetXY(50, 95);
$pdf->Write(8, $data['address']);

// ---- 4) 電話番号
$pdf->SetXY(140, 100);
$pdf->Write(8, $data['tel']);

// ---- 5) 銀行名
$pdf->SetXY(105, 145);
$pdf->Write(8, $data['bank_name']);

// ---- 6) 支店名
$pdf->SetXY(150, 145);
$pdf->Write(8, $data['branch_name']);

// ---- 7) 預金種目（普通 / 当座 → マル）
if ($data['account_type'] === '普通') {
    $pdf->SetXY(103, 160);
} else {
    $pdf->SetXY(128, 160);
}
$pdf->Write(8, '〇');

// ---- 8) 口座番号（記号）
$pdf->SetXY(150, 162);
$pdf->Write(8, $data['account_no']);

// ---- 9) 口座名義（フリガナ）
$pdf->SetXY(35, 170);
$pdf->Write(8, $data['account_kana']);

// ---- 10) 口座名義（漢字）
$pdf->SetXY(35, 190);
$pdf->Write(8, $data['account_name']);
// 保存先ファイルパス（storage/app/public 内など）
$file_path = storage_path('app/public/generated/bank-info-' . time() . '.pdf');

// 'F' はファイルに直接保存する
$pdf->Output($file_path, 'F');

// ブラウザで表示したい場合は、保存したファイルを読み込む
return response()->file($file_path, [
    'Content-Type' => 'application/pdf'
]);
    }

}

