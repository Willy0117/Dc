<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ライセンス証送付のご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $organization->name }} ご担当者様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    この度はライセンスのご契約ありがとうございます。<br>
    ライセンス証を添付いたしましたのでご確認ください。
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

