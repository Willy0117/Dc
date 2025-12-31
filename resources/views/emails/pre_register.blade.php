<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>メールアドレス確認</title>
</head>
<body>
    <p>{{ $email }} 宛に会員登録の確認です。</p>

    <p>下記URLから規約同意・登録を開始してください。</p>

    <p>
        <a href="{{ $url }}">{{ $url }}</a>
    </p>

    <p>※ このURLは24時間有効です。</p>

    <hr>

    <p>このメールは <strong>{{ config('mail.from.address') }}</strong> から送信されています。<br>
       このメールに返信しないでください。</p>

    <p>もし心当たりがない場合は、このメールを破棄してください。</p>

    <p>よろしくお願いいたします。<br><br><br>
    {{ config('mail.from.name') }}</p>
</body>
</html>

