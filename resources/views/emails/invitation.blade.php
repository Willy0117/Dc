<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>ライセンス契約お申込みのご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $organization->name }} ご担当者様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    この度は動注治療ライセンスにご興味をお持ちいただきありがとうございます。<br>
    下記URLよりお申込み手続きをお願いいたします。
  </p>

  <p>
    <a href="{{ $url }}" style="display:inline-block; padding:12px 24px; background:#4F46E5; color:#fff; text-decoration:none; border-radius:6px;">
      お申込みはこちら
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
