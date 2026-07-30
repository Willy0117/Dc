@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-6 text-center">
    <h1 class="text-2xl font-bold mb-4">ご購入ありがとうございます</h1>
    <p class="mb-2">決済が完了しました。</p>

    @if ($watchUrl)
        <p class="mb-4">以下のボタンから視聴サイトへお進みください。</p>
        <a href="{{ $watchUrl }}" class="inline-block bg-blue-600 text-white px-6 py-3 rounded font-semibold mb-4">
            視聴サイトへ進む
        </a>
        <p class="text-sm text-gray-500">動画の視聴サイトURLは、ご登録のメールアドレス宛にお送りする確認メールにも記載していますので、後からでもアクセスいただけます。</p>
    @else
        <p></p>
        <p class="text-sm text-gray-500 mt-4">メールが届かない場合は、迷惑メールフォルダをご確認のうえ、サポート窓口までご連絡ください。</p>
    @endif
</div>
@endsection
