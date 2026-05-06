<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>入会申込完了のお知らせ</title>
</head>
<body>
    <p>
       {{ $member->name }} 御中<br> 
       {{ $member->last_name }} {{ $member->first_name }} 様
    </p>

    <p>
        このたびは、入会申込をいただき誠にありがとうございます。<br>
        以下の内容で申込を受け付けました。
    </p>

    <hr>

    <p>
        ■ 申込区分：<br>
        @if(($member->type ?? null) === 1)
            本人申請
        @else
            代理人申請
        @endif
    </p>

    <p>
        ■ 申込日：<br>
        {{ now()->format('Y年m月d日') }}
    </p>

    <hr>

    <p>
        内容を確認のうえ、事務局にて手続きを進めさせていただきます。<br>
        今しばらくお待ちください。
    </p>

    <p>
        ※ 本メールは自動送信です。<br>
        本メールに心当たりがない場合は、このメールを破棄してください。
    </p>

    <hr>

    <p>
        このメールは <strong>{{ config('mail.from.address') }}</strong> から送信されています。<br>
        このメールに返信しないでください。
    </p>

    <p>
        よろしくお願いいたします。<br><br>
        {{ config('mail.from.name') }}
    </p>
</body>
</html>