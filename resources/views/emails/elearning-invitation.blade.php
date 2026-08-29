<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>e-ラーニング受講のご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $memberName }} 先生</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    動注ライセンス契約手続きの一環として、簡易e-ラーニングの受講をお願いしております。<br>
    下記URLより受講手続きをお願いいたします（所要時間の目安：5〜10分程度）。
  </p>

  <p>
    <a href="{{ $url }}" style="display:inline-block; padding:12px 24px; background:#4F46E5; color:#fff; text-decoration:none; border-radius:6px;">
      e-ラーニングを受講する
    </a>
  </p>

  <p style="font-size: 12px; color: #666;">
    ※ 上記ボタンが表示されない場合は以下のURLをコピーしてブラウザに貼り付けてください。<br>
    {{ $url }}
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