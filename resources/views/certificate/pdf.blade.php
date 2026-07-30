<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page {
        size: 8.27in 11.69in;
        margin: 0;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        padding: 0;
        font-family: "IPAexGothic", sans-serif;
        position: relative;
        width: 8.27in;
        height: 11.69in;
    }
    .bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 8.27in;
        height: 11.69in;
        z-index: 0;
    }
    .content {
        position: relative;
        z-index: 1;
        padding-top: 1.5in;
    }
    .faux-bold {
        text-shadow: 0.4pt 0 0 currentColor, -0.4pt 0 0 currentColor, 0 0.4pt 0 currentColor, 0 -0.4pt 0 currentColor;
    }
    .title {
        text-align: center;
        font-size: 42pt;
        letter-spacing: 8px;
        margin: 0 0 0.65in 0;
    }
    .recipient {
        font-size: 24pt;
        margin: 0 0 0.5in 1.05in;
    }
    .lead {
        width: 6.15in;
        font-size: 18pt;
        line-height: 1.8;
        margin: 0 0 0.55in 1.05in;
    }
    table.cert-table {
        width: 6.15in;
        table-layout: fixed;
        border-collapse: collapse;
        font-size: 14pt;
        margin: 0 0 0.5in 1.05in;
    }
    table.cert-table th, table.cert-table td {
        border: 1px solid #333;
        padding: 10px 14px;
        text-align: left;
        vertical-align: top;
        word-wrap: break-word;
        overflow-wrap: break-word;
        word-break: break-all;
    }
    table.cert-table th {
        width: 22%;
        background: #f5f5f5;
        font-weight: bold;
        white-space: normal;
    }
    .issue-date {
        width: 6.15in;
        text-align: right;
        font-size: 16pt;
        margin: 0 0 0.3in 1.05in;
    }
    table.signature {
        width: 6.15in;
        font-size: 16pt;
        border-collapse: collapse;
        margin: 0 0 0.4in 1.05in;
    }
    table.signature td {
        border: none;
        padding: 0;
        vertical-align: middle;
    }
    table.signature .text-cell {
        text-align: right;
        padding-right: 12px;
    }
    table.signature .org-name {
        display: block;
    }
    table.signature .president {
        display: block;
        margin-top: 6px;
        text-align: right;
    }
    table.signature .hanko-cell {
        text-align: right;
        width: 1in;
    }
    table.signature .hanko {
        width: 0.95in;
        height: 0.95in;
    }
    .cert-number {
        position: absolute;
        bottom: 0.3in;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 10pt;
        color: #777;
    }
</style>
</head>
<body>

    <img class="bg" src="{{ public_path('images/certificate/background.jpeg') }}">

    <div class="content">
        <div class="title faux-bold">受講証明書</div>

        <div class="recipient faux-bold">{{ $order->customer_name }} 殿</div>

        <div class="lead">
            下記講義について、所定の受講要件を満たし、<br>
            受講を完了されたことを証明いたします。
        </div>

        <table class="cert-table">
            <tr>
                <th>セミナー名</th>
                <td>2026年度医療安全管理者のための<br>オンデマンドセミナー</td>
            </tr>
            <tr>
                <th>講義名</th>
                <td>{{ $order->videoSet->category }}<br>{{ $video->title }}</td>
            </tr>
            <tr>
                <th>講師名</th>
                <td>{!! str_replace(' ', '&nbsp;', e($video->speaker_name)) !!}</td>
            </tr>
            <tr>
                <th>講義時間</th>
                <td>{{ $video->duration_minutes }}分</td>
            </tr>
            <tr>
                <th>主催</th>
                <td>医療の質・安全学会　教育委員会</td>
            </tr>
        </table>

        <div class="issue-date">{{ $issuedAt->format('Y年n月j日') }}</div>

        <table class="signature">
            <tr>
                <td class="text-cell">
                    <span class="org-name faux-bold">一般社団法人　医療の質・安全学会</span>
                    <span class="president">理事長　中島　和江</span>
                </td>
                <td class="hanko-cell">
                    <img class="hanko" src="{{ public_path('images/certificate/logo.png') }}">
                </td>
            </tr>
        </table>
    </div>

    @if($certificateNumber)
    <div class="cert-number">証明書番号：{{ $certificateNumber }}</div>
    @endif

</body>
</html>