<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>お支払いのご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $organization->name }} ご担当者様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    この度はライセンスのお申込みありがとうございます。<br>
    下記リンクよりお支払い手続きをお願いいたします。
  </p>

  <p>
    <strong>ご請求額：{{ number_format($invoice->amount) }}円（税込）</strong><br>
    <strong>お支払い期限：{{ \Carbon\Carbon::parse($invoice->due_date)->format('Y年m月d日') }}</strong>
  </p>

  <p>
    <a href="{{ $payment_link }}" style="display:inline-block; padding:12px 24px; background:#4F46E5; color:#fff; text-decoration:none; border-radius:6px;">
      お支払いはこちら
    </a>
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