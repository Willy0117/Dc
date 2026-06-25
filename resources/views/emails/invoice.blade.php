<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>請求書送付のご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $organization->name }} ご担当者様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    この度はライセンスのお申込みありがとうございます。<br>
    請求書を添付いたしましたのでご確認ください。
  </p>

  <p>
    <strong>請求書番号：{{ $invoice->invoice_no }}</strong><br>
    <strong>ご請求額：{{ number_format($invoice->amount) }}円（税込）</strong><br>
    <strong>お支払い期限：{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y年m月d日') }}</strong>
  </p>

  <p>
    お振込みの際、ご契約のクリニック名の明記をお願いいたします。<br>
    大変恐れ入りますが、振込手数料は貴院にてご負担いただけますようお願い申し上げます。
  </p>

  <p>
      このメールは <strong>{{ config('mail.from.address') }}</strong> から送信されています。<br>
      このメールに返信しないでください。
  </p>

  <p>
    ご不明な点がございましたら、下記より、お気軽にお問い合わせください。<br><br>
    {{ config('mail.from.name') }}<br>
    <a href="mailto:license_okunoclinic@alivio-japan.com">license_okunoclinic@alivio-japan.com</a>
  </p>

</body>
</html>