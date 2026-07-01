<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title>{{ $subject }}</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.8;">
  <p>{{ $organization_name }} ご担当者様</p>

  <p>
    平素よりお世話になっております。<br>
    {{ config('mail.from.name') }}でございます。
  </p>

  <div>
    {!! nl2br(e($body)) !!}
  </div>

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