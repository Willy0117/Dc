@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto p-4 sm:p-6 text-center">
    <div class="bg-white border rounded-2xl shadow-sm p-6 md:p-8">
        <h1 class="text-xl md:text-2xl font-bold text-sky-900 mb-2">テスト結果</h1>
        <p class="text-gray-600 mb-4">{{ $video->title }}</p>
        <p class="text-xl mb-4">得点：{{ $attempt->score_percent }}%</p>

        @if ($attempt->passed)
            <p class="text-emerald-600 font-semibold mb-2">確認テストに合格しました。</p>
            <p class="text-sm text-gray-600">この講義の受講証明書をメールにてお送りしましたのでご確認ください。</p>
        @else
            <p class="text-red-600 font-semibold mb-2">
                合格ラインの{{ $order->videoSet->passing_score }}%に届きませんでした。
            </p>
            <a href="{{ route('quiz.show', [$order->access_token, $video->id]) }}" class="text-sky-700 underline">
                もう一度受験する
            </a>
        @endif

        <div class="mt-6">
            <a href="{{ route('watch.index', $order->access_token) }}" class="text-sky-700 underline text-sm">
                動画一覧に戻る
            </a>
        </div>
    </div>
</div>
@endsection
