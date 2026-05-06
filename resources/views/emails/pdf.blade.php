<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>ご注文いただいたきありがとうございます.</title>
</head>
<body>
    <p>
       {{ $application->organization->name }} 御中 {{ $application->staff_name }} 様<br> 
    </p>

    <p>
        この度は、ご注文をいただき誠にありがとうございます。<br>

        以下の内容でご注文を受付ました。
    </p>

    <hr>

    <p>
        ■ 申込日：{{ $application->created_at }} <br>

        ■ 納期日：{{ $application->delivery_date }} <br>
        <br>
        申込名（ふりがな）<br>
        {{ $application->deceased_furigana }} 様<br>
        <br>
        申込名<br>
        {{ $application->last_name }} {{ $application->first_name }} 様<br>
        <br>
        性別<br>
        {{ $application->gender }} <br>
    </p>

    <hr>

    <p>
        内容を確認のうえ、制作を進めさせていただきます。<br>
        今、しばらくお待ちください。
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