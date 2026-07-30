@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">{{ $videoSet->name }}</h1>
    <p class="mb-6 whitespace-pre-line">{{ $videoSet->description }}</p>
    <p class="text-xl font-semibold mb-6">¥{{ number_format($videoSet->price_jpy) }}（税込）</p>

    <form method="POST" action="{{ route('checkout.start', $videoSet) }}" class="space-y-4">
        @csrf
        <div>
            <label class="block mb-1">お名前</label>
            <input type="text" name="name" class="border rounded w-full p-2" required>
        </div>
        <div>
            <label class="block mb-1">メールアドレス（視聴URL送付先）</label>
            <input type="email" name="email" class="border rounded w-full p-2" required>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded font-semibold">
            購入手続きへ進む
        </button>
    </form>
</div>
@endsection
