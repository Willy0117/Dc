@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-4 sm:p-6">
    <h1 class="text-xl md:text-2xl font-bold text-sky-900 mb-2">確認テスト</h1>
    <p class="text-gray-600 mb-1">{{ $video->title }}</p>
    <p class="text-sm text-gray-500 mb-6" id="progress-label">
        設問 <span id="current-index">1</span> / {{ $totalQuestions }}
    </p>

    <!-- 設問表示エリア -->
    <div id="question-area" class="bg-white border rounded-2xl shadow-sm p-4 md:p-5">
        <p class="font-semibold mb-3 text-sky-900" id="question-text">
            {{ $question['question_text'] }}
            <span id="multiple-label" class="text-sm text-sky-600 font-normal" style="{{ $question['is_multiple'] ? '' : 'display:none;' }}">（複数選択可）</span>
        </p>
        <div id="choices-area">
            @foreach ($question['choices'] as $choice)
                <label class="block mb-1 text-sm">
                    <input
                        type="{{ $question['is_multiple'] ? 'checkbox' : 'radio' }}"
                        name="choice"
                        value="{{ $choice['id'] }}"
                    >
                    {{ $choice['choice_text'] }}
                </label>
            @endforeach
        </div>
    </div>

    <button id="submit-btn" type="button" class="mt-4 bg-pink-500 hover:bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold">
        解答する
    </button>

    <!-- 正解時：解説表示エリア（「次へ」を押すまで結果には進まない） -->
    <div id="explanation-area" class="hidden bg-emerald-50 border border-emerald-200 rounded-2xl p-4 md:p-5 mt-4">
        <p class="text-emerald-700 font-semibold mb-2">正解です！</p>
        <p class="text-sm text-gray-700 whitespace-pre-line" id="explanation-text"></p>
        <button id="continue-btn" type="button" class="mt-4 bg-sky-600 hover:bg-sky-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold">
            次へ
        </button>
    </div>

    <!-- 結果表示エリア（不合格・最終合格時にここへ切り替え） -->
    <div id="result-area" class="hidden bg-white border rounded-2xl shadow-sm p-6 text-center mt-4"></div>
</div>

<script>
    const token = @json($order->access_token);
    const videoId = @json($video->id);
    const csrfToken = @json(csrf_token());
    const answerUrl = @json(route('quiz.answer', [$order->access_token, $video->id]));
    const watchUrl = @json(route('watch.index', $order->access_token));

    let isMultiple = @json($question['is_multiple']);
    let pendingResult = null; // 「次へ」を押した時に表示する最終結果（合格時）を一時保持

    function renderQuestion(question) {
        isMultiple = question.is_multiple;
        document.getElementById('current-index').textContent = question.index + 1;
        document.getElementById('question-text').firstChild.textContent = question.question_text + ' ';
        document.getElementById('multiple-label').style.display = question.is_multiple ? '' : 'none';

        const choicesArea = document.getElementById('choices-area');
        choicesArea.innerHTML = question.choices.map(choice => `
            <label class="block mb-1 text-sm">
                <input type="${question.is_multiple ? 'checkbox' : 'radio'}" name="choice" value="${choice.id}">
                ${choice.choice_text}
            </label>
        `).join('');

        document.getElementById('question-area').classList.remove('hidden');
        document.getElementById('submit-btn').classList.remove('hidden');
        document.getElementById('explanation-area').classList.add('hidden');
    }

    function getSelectedChoiceIds() {
        return Array.from(document.querySelectorAll('input[name="choice"]:checked')).map(el => el.value);
    }

    function showResult(html) {
        document.getElementById('question-area').classList.add('hidden');
        document.getElementById('submit-btn').classList.add('hidden');
        document.getElementById('explanation-area').classList.add('hidden');
        const resultArea = document.getElementById('result-area');
        resultArea.classList.remove('hidden');
        resultArea.innerHTML = html;
    }

    function showExplanation(explanationText) {
        document.getElementById('submit-btn').classList.add('hidden');
        const area = document.getElementById('explanation-area');
        area.classList.remove('hidden');
        document.getElementById('explanation-text').textContent = explanationText || '';
    }

    document.getElementById('submit-btn').addEventListener('click', () => {
        const selected = getSelectedChoiceIds();
        if (selected.length === 0) {
            alert('選択肢を選んでください。');
            return;
        }

        fetch(answerUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ choices: selected }),
        })
        .then(res => res.json())
        .then(data => {
            if (!data.correct) {
                // 不正解 → 即座に不合格確定
                showResult(`
                    <p class="text-red-600 font-semibold text-lg mb-2">不正解です。</p>
                    <p class="text-sm text-gray-600 mb-4">最初の設問からもう一度解答してください。</p>
                    <button onclick="location.reload()" class="inline-block bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg text-sm font-semibold mr-2">確認テストをやり直す</button>
                    <a href="${watchUrl}" class="inline-block text-sky-700 underline text-sm mt-3">動画一覧に戻る</a>
                `);
                return;
            }

            // 正解 → まず解説を表示し、「次へ」待ちにする
            pendingResult = data;
            showExplanation(data.explanation);
        })
        .catch(() => {
            alert('通信エラーが発生しました。もう一度お試しください。');
        });
    });

    document.getElementById('continue-btn').addEventListener('click', () => {
        if (!pendingResult) return;

        if (pendingResult.finished) {
            showResult(`
                <p class="text-emerald-600 font-semibold text-lg mb-2">確認テストに合格しました。</p>
                <p class="text-sm text-gray-600 mb-4">受講証明書をメールにてお送りしました。</p>
                <a href="${pendingResult.certificate_download_url}" class="inline-block bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-semibold mr-2">証明書をダウンロード</a>
                <a href="${watchUrl}" class="inline-block text-sky-700 underline text-sm mt-3">動画一覧に戻る</a>
            `);
        } else {
            renderQuestion(pendingResult.question);
        }

        pendingResult = null;
    });
</script>
@endsection