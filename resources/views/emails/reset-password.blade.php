<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>パスワード設定のご案内</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $notifiable->name }} 様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <p>
    MyPage(会員専用ページ)のパスワード設定用のご案内です。<br>
    下記URLよりパスワードの設定手続きをお願いいたします。
  </p>

  <p style="background:#F7F5F0; border-left:3px solid #C9A227; padding:12px 16px;">
    ログインID(ユーザー名): <strong>{{ $notifiable->username }}</strong><br>
    ログイン時は、上記のユーザー名をご利用いただけます。
  </p>

  <p>
    <a href="{{ $url }}" style="display:inline-block; padding:12px 24px; background:#4F46E5; color:#fff; text-decoration:none; border-radius:6px;">
      パスワードを設定する
    </a>
  </p>

  <p style="font-size: 12px; color: #666;">
    ※ 上記ボタンが表示されない場合は以下のURLをコピーしてブラウザに貼り付けてください。<br>
    {{ $url }}<br><br>
    ※ このリンクの有効期限は60分です。
  </p>

  <p>
      このメールは <strong>{{ config('mail.from.address') }}</strong> から送信されています。<br>
      このメールに返信しないでください。
  </p>

  <p>
    心当たりがない場合は、本メールを破棄してください。<br>
    ご不明な点がございましたら、下記より、お気軽にお問い合わせください。<br><br>
    {{ config('mail.from.name') }}<br>
    <a href="mailto:{{ config('mail.from.address') }}">{{ config('mail.from.address') }}</a>
  </p>
</body>
</html>