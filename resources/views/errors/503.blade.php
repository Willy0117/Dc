<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>只今メンテナンス中です</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-sky-50 to-white min-h-screen flex items-center justify-center">

    <div class="max-w-lg w-full mx-4 text-center">
        <img src="/images/logo.png" alt="一般社団法人医療の質・安全学会 ロゴ" class="h-16 md:h-20 mx-auto mb-6">

        <div class="bg-white border rounded-2xl shadow-sm p-8 space-y-4">
            <h1 class="text-xl md:text-2xl font-bold text-sky-900">
                只今メンテナンス中です
            </h1>
            <p class="text-slate-600 text-sm md:text-base leading-relaxed">
                {{ $exception->getMessage() ?: 'システムメンテナンスのため、一時的にサイトをご利用いただけません。' }}
            </p>
            <p class="text-slate-500 text-sm">
                ご不便をおかけし申し訳ございません。しばらく経ってから再度アクセスしてください。
            </p>

            @isset($retryAfter)
                <p class="text-xs text-slate-400">
                    復旧予定：約{{ $retryAfter }}秒後
                </p>
            @endisset
        </div>

        <footer class="mt-6 text-xs text-slate-400 space-y-1">
            <p>一般社団法人医療の質・安全学会 事務局</p>
            <p>〒113-0033 東京都文京区本郷2-29-1 渡辺ビル201</p>
        </footer>
    </div>

</body>
</html>
