@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-4 sm:p-6">
    @if (session('info'))
        <div class="bg-sky-50 border border-sky-200 text-sky-800 rounded-lg p-3 text-sm mb-4">
            {{ session('info') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-3 text-sm mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <h1 class="text-xl md:text-2xl font-bold text-sky-900 mb-2">e-ラーニング受講（契約時）</h1>
    <p class="text-gray-600 mb-1">{{ $memberName }} 先生</p>

    @if ($isCompleted)
        <div class="bg-white border rounded-2xl shadow-sm p-6 text-center mt-4">
            <p class="text-emerald-600 text-lg font-semibold mb-1">✔ 受講済みです</p>
            <p class="text-sm text-gray-500">受講日時：{{ $completedAt }}</p>
        </div>
    @else
        <p class="text-sm text-gray-500 mb-6">
            全{{ $questions->count() }}問すべてに正解すると受講完了となります。
        </p>

        <form method="POST" action="{{ route('elearning-invitations.submit', $token) }}">
            @csrf

            @foreach ($questions as $q)
                <input type="hidden" name="question_ids[]" value="{{ $q['id'] }}">
                <div class="mb-6 bg-white border rounded-2xl shadow-sm p-4 md:p-5">
                    <p class="font-semibold mb-3 text-sky-900">
                        {{ $loop->iteration }}. {{ $q['text'] }}
                    </p>
                    <div>
                        @foreach ($q['choices'] as $key => $text)
                            <label class="block mb-1 text-sm">
                                <input
                                    type="radio"
                                    name="answers[{{ $q['id'] }}]"
                                    value="{{ $key }}"
                                    required
                                >
                                {{ $text }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <button type="submit" class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-3 rounded-lg font-semibold">
                解答する
            </button>
        </form>
    @endif
</div>
@endsection