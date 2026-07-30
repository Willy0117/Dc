@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-4 sm:p-6">
    @if (session('info'))
        <div class="bg-sky-50 border border-sky-200 text-sky-800 rounded-lg p-3 text-sm mb-4">
            {{ session('info') }}
        </div>
    @endif
    <h1 class="text-xl md:text-2xl font-bold text-sky-900 mb-6">セット{{ $order->videoSet->name }}：{{ $order->videoSet->category }}</h1>

    @foreach ($videos as $video)
        @php
            $isCompleted = in_array($video->id, $completedVideoIds);
            $isPassed = in_array($video->id, $passedVideoIds);
        @endphp
        <div class="mb-6 bg-white border rounded-2xl shadow-sm p-4 md:p-5">
            <h2 class="font-semibold mb-2 text-sky-900">
                {{ $video->title }}
                @if ($video->speaker_name)
                    <span class="text-sm text-gray-500">（{{ $video->speaker_name }}）</span>
                @endif
                @if ($isCompleted)
                    <span class="text-emerald-600 text-sm ml-2">✔ 視聴完了</span>
                @endif
                @if ($isPassed)
                    <span class="text-sky-600 text-sm ml-2">🏅 証明書発行済み</span>
                @endif
            </h2>
            <div style="position:relative;padding-top:56.25%;">
                <iframe
                    id="vimeo-player-{{ $video->id }}"
                    src="{{ $video->embedUrl() }}"
                    style="position:absolute;top:0;left:0;width:100%;height:100%;"
                    frameborder="0"
                    allow="autoplay; fullscreen; picture-in-picture"
                    allowfullscreen
                    data-video-id="{{ $video->id }}"
                ></iframe>
            </div>

            <div class="mt-3 flex flex-wrap gap-2">
                <div class="flex flex-wrap gap-2" id="quiz-actions-{{ $video->id }}">
                    @if ($isPassed)
                        <a href="{{ route('certificate.download', [$order->access_token, $video->id]) }}"
                           class="inline-block bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            証明書をダウンロード
                        </a>
                    @elseif ($isCompleted)
                        <a href="{{ route('quiz.show', [$order->access_token, $video->id]) }}"
                           class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                            この講義のテストを受ける
                        </a>
                    @else
                        <p class="text-sm text-gray-500">動画を最後まで視聴すると、確認テストのリンクが表示されます。</p>
                    @endif
                </div>

                @if ($video->material_s3_key)
                    <a href="{{ route('video.material', [$order->access_token, $video->id]) }}"
                       class="inline-block bg-white border border-sky-600 text-sky-700 hover:bg-sky-50 px-4 py-2 rounded-lg text-sm font-semibold">
                        資料をダウンロード
                    </a>
                @endif
            </div>
        </div>
    @endforeach
</div>

<script src="https://player.vimeo.com/api/player.js"></script>
<script>
    const token = @json($order->access_token);
    const csrfToken = @json(csrf_token());
    const quizUrlBase = @json(route('quiz.show', [$order->access_token, '__VIDEO_ID__']));

    document.querySelectorAll('iframe[data-video-id]').forEach((iframe) => {
        const player = new Vimeo.Player(iframe);
        const videoId = iframe.dataset.videoId;

        player.on('ended', () => {
            fetch(`/watch/${token}/complete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ video_id: videoId }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.ok) {
                    const actionsEl = document.getElementById(`quiz-actions-${videoId}`);
                    if (actionsEl && !actionsEl.querySelector('a')) {
                        const quizUrl = quizUrlBase.replace('__VIDEO_ID__', videoId);
                        actionsEl.innerHTML = `
                            <a href="${quizUrl}" class="inline-block bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">この講義のテストを受ける</a>
                        `;
                    }
                }
            });
        });
    });
</script>
@endsection