<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ライセンス契約更新のご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $organization->name }} ご担当者様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    ライセンス契約の更新日が近づいてまいりましたのでご連絡いたします。<br>
    更新日：<strong>{{ \Carbon\Carbon::parse($new_contract_date)->format('Y年m月d日') }}</strong>
  </p>

  <p>
    お手続きにつきましては、担当者よりご案内いたします。<br>
    ご不明な点がございましたら、下記よりお気軽にお問い合わせください。
  </p>

  <p>
    このメールは <strong>{{ config('mail.from.address') }}</strong> から送信されています。<br>
    このメールに返信しないでください。
  </p>

  <p>
    {{ config('mail.from.name') }}<br>
    <a href="mailto:license_okunoclinic@alivio-japan.com">license_okunoclinic@alivio-japan.com</a>
  </p>
</body>
</html>