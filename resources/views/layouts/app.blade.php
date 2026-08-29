<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', '動注ライセンス講習・症例報告サイト')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-b from-sky-50 to-white text-gray-900 flex flex-col min-h-screen">

    <header class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-6 py-4 flex items-center gap-3">
            <span class="text-sm md:text-base font-bold text-sky-900">動注ライセンス講習・症例報告サイト</span>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 text-center text-xs md:text-sm text-gray-500 py-6 mt-8">
        <p>{{ config('mail.from.name') }}</p>
        <p class="mt-1">
            <a href="mailto:license_okunoclinic@alivio-japan.com" class="text-sky-700 underline">
                お問い合わせ：license_okunoclinic@alivio-japan.com
            </a>
        </p>
    </footer>

</body>
</html>