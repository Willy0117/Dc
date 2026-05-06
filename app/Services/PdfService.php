<?php

namespace App\Services;

use setasign\Fpdi\Tcpdf\Fpdi;
use Illuminate\Support\Facades\Storage;

class PdfService
{
    protected $disk;

    public function __construct()
    {
        $this->disk = config('filesystems.default');
    }

    /**
     * Application の情報から PDF を作成し、文字列で返す
     */
    public function createApplicationPdf($data, $canvasFilePath)
    {
             // FPDI + TCPDF
        $pdf = new Fpdi();
        // ヘッダーフッター消し
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        // ページ追加
        $pdf->AddPage();

        // 既存PDFテンプレート読み込み
        $templatePath = Storage::path('templates/order_sheet/poem.pdf');

        $pageCount = $pdf->setSourceFile($templatePath);
        $tpl = $pdf->importPage(1);
        $pdf->useTemplate($tpl);

        // TCPDF同梱の日本語フォント
        $pdf->SetFont('kozminproregular', '', 11); // もしくは cid0jp
                // 受注コード
        $pdf->SetXY(158, 18);
        $pdf->Write(8, $data->order_code);
        //納品時間    
        $pdf->SetXY(50, 48);
        $pdf->Write(8, ($data['delivery_date']??now())->format('Y年m月d日 H時i分'));
        //申込時間    
        $pdf->SetXY(135, 48);
        $pdf->Write(8, now()->format('Y年m月d日 H時i分'));

        $pdf->SetXY(65, 62);
        $pdf->Write(8, $data['staff_name']??'山田　太郎');
        $pdf->SetXY(65, 69);
        $pdf->Write(8, $data->user);
        $pdf->SetXY(65, 76);
        $pdf->Write(8, ($data['funeral_datetime']??now())->format('Y年m月d日 H時i分'));
        $pdf->SetXY(65, 83);
        $pdf->Write(8, '名前の詩');
        $pdf->SetXY(65, 90);
        $pdf->Write(8, $data['deceased_furigana']??'やまだ　たろう');
        $pdf->SetFont('kozminproregular', '', 14); // もしくは cid0jp

        $name = ($data['last_name']??'山田') . ' ' . ($data['first_name']??'太郎');
        $pdf->SetXY(65,  99);
        $pdf->Write(8, $name);
        $pdf->SetFont('kozminproregular', '', 11); // もしくは cid0jp

        $pdf->SetXY(65, 107);
        $pdf->Write(8, $data['gender']??'男');
        $pdf->SetXY(65, 114);
        $pdf->Write(8, ($data['age_at_death']??'90') . '　歳');
        $pdf->SetXY(65, 121);

        $spouse = $data['spouse_status']??'none';
        $spouse_status = [
            'none' => '無',
            'alive' => '有',
            'deceased' => '死別',
        ];
        $pdf->Write(8, $spouse_status[$spouse]);
        $pdf->SetXY(65, 128);
        $pdf->Write(8, $data['children_count']??'未記入');
        $pdf->SetXY(65, 135);
        $pdf->Write(8, $data['grandchildren_count']??'未記入');
        $pdf->SetXY(65, 142);
        $pdf->Write(8, $data['chief_mourner_name']??'未記入');
        $pdf->SetXY(65, 149);
        $pdf->Write(8, $data['relationship_to_deceased']??'未記入');

        $text_color = $data['text_color']??'brown';
        $colorLabels = [
            'brown'  => '茶',
            'green'  => '緑',
            'pink'   => 'ピンク',
            'blue'   => '青',
            'orange' => 'オレンジ',
            'yellow' => '黄色',
        ];
        $pdf->SetXY(65, 155);
        $pdf->Write(8, $colorLabels[$text_color]);

        $bg_color = $data['bg_color']??'none';
        $colorLabels = [
            'none'   => 'なし',
            'green'  => '緑',
            'pink'   => 'ピンク',
            'blue'   => '青',
            'orange' => 'オレンジ',
        ];
        $pdf->SetXY(65, 162);
        $pdf->Write(8, $colorLabels[$bg_color]);

        $traitsOptions = [
            '優しい','明朗','温和','誠実','思いやり','面倒見良い','忍耐強い',
            '親切','真面目','努力家','積極的','責任感が強い','世話好き'
        ];

        $traits = $data['traits'] ?? [];

        if (is_string($traits)) {
            $traits = json_decode($traits, true) ?? [];
        }

        if (is_string($traits)) {
            $traits = json_decode($traits, true) ?? [];
        }
        $traitsText = '';

        foreach ($traitsOptions as $trait) {
            $traitsText .= in_array($trait, $traits) ? '■'.$trait.'  ' : '□'.$trait.'  ';
        }
        $pdf->SetXY(65, 171);
        $pdf->MultiCell(120,7,$traitsText);

        $pdf->SetXY(65, 197);
        $pdf->MultiCell(120, 8, $data['special_note'] ?? 'なし');

        $pdf->SetXY(65, 224);
        $pdf->MultiCell(120, 8, $data['remarks'] ?? 'なし');

        if ($canvasFilePath) {

            $canvasPath = Storage::disk($this->disk)->path($canvasFilePath);

            if (file_exists($canvasPath)) {
                $pdf->Image($canvasPath, 150, 108, 32);
            }
        }
        // PDFデータを文字列で返す
        return $pdf->Output('', 'S'); // 'S'で文字列取得
    }

}