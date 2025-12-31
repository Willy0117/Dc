<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use TCPDF_FONTS;
use setasign\Fpdi\Tcpdf\Fpdi;

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
            'agree' => $request->agree_terms,
            'affiliate' => $request->affiliate,
            'agree_at'  => now()->toDateTimeString(), // 追加
        ]);

        return redirect()->route('members.register.register', ['token' => $token]);
    }

    // 3. Register 入力ページ
    public function showRegisterForm($token)
    {
        return Inertia::render('Members/Register', [
            'token'        => $token,
            'agree'  => session('agree_terms'),
            'affiliate'  => session('affiliate'),
            'agree_at'   => session('agree_date'), // ← ここ追加
        ]);
    }

    // 4. 完了処理（PDF2点）
    public function completeRegistration(Request $request, $token)
    {
        $request->validate([
            'history_certificate' => 'required|mimes:pdf',
            'bank_transfer_request' => 'required|mimes:pdf',
        ]);

        return back()->with('success', '登録が完了しました');
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
            'bank_name'    => 'required|string',
            'branch_name'  => 'required|string',
            'account_type' => 'required|string',
            'account_no'   => 'required|string',
            'account_kana'   => 'required|string',
            'account_name' => 'required|string',
        ]);
        // セッションに保存
        session(['member_form' => $data]);

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

    public function pdfPreview(Request $request)
    {
        return Inertia::render('Members/PdfPreview', [
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

