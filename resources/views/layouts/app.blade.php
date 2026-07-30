<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', '講習・視聴サイト')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-sky-50 to-white text-gray-900 flex flex-col min-h-screen">

    <header class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center gap-3">
            <img src="/images/logo.png" alt="一般社団法人医療の質・安全学会 ロゴ" class="h-10 md:h-12">
            <span class="text-sm md:text-base font-bold text-sky-900">これから医療安全管理に携わる方のためのオンデマンドセミナー</span>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 text-center text-xs md:text-sm text-gray-500 py-6 mt-8">
        <p>一般社団法人医療の質・安全学会 事務局</p>
        <p>〒113-0033 東京都文京区本郷2-29-1 渡辺ビル201</p>
        <p class="mt-1">
            <a href="https://jsqsh.jp/faq/consultation" target="_blank" class="text-sky-700 underline">お問合せフォーム</a>
        </p>
    </footer>

</body>
</html>
