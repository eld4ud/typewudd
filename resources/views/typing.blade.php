@extends('layout')
@section('title', 'Typing Test')

@section('content')
    <section class="pagehead pagehead-typing">
        <div class="eyebrow">PRACTICE / IMPROVE / REPEAT</div>
        <h1>Typing Test.</h1>
        <p>Mulai mengetik untuk menjalankan timer. Login agar hasil tes tersimpan.</p>
    </section>

    <section class="panel typing-panel" id="typingApp" data-authenticated="{{ auth()->check() ? '1' : '0' }}">
        <div class="settings">
            <label>
                Mode
                <select id="mode">
                    <option value="time">Time</option>
                    <option value="words">Words</option>
                    <option value="practice">Practice</option>
                </select>
            </label>

            <label>
                Difficulty
                <select id="difficulty">
                    <option value="easy">Easy</option>
                    <option value="normal" selected>Normal</option>
                    <option value="hard">Hard</option>
                    <option value="expert">Expert</option>
                </select>
            </label>

            <label>
                Language
                <select id="language">
                    <option value="en">English</option>
                    <option value="id">Indonesia</option>
                </select>
            </label>

            <label id="targetLabel">
                Duration
                <select id="target">
                    <option value="15">15s</option>
                    <option value="30">30s</option>
                    <option value="60" selected>60s</option>
                    <option value="120">120s</option>
                </select>
            </label>
        </div>

        <div class="metrics typing-metrics" aria-label="Typing metrics">
            <div>
                <b id="timer">60</b>
                <small>SECONDS</small>
            </div>
            <div>
                <b id="wpm">0</b>
                <small>WPM</small>
            </div>
            <div>
                <b id="accuracy">100%</b>
                <small>ACCURACY</small>
            </div>
        </div>

        <div class="typing-stage" id="typingStage" tabindex="0" aria-label="Klik untuk mulai mengetik">
            <div class="inline-typing-text" id="prompt" aria-hidden="true"></div>
            <textarea
                id="typed"
                class="typing-capture"
                spellcheck="false"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                aria-label="Ketik teks yang ditampilkan"
                disabled
            ></textarea>
        </div>

        <p class="typing-hint" id="hint" aria-live="polite">Memuat teks...</p>

        <div class="actions typing-actions">
            <button class="btn" id="restart" type="button">Restart ↻</button>
            <button class="btn outline" id="pause" type="button" hidden>Pause practice</button>
        </div>

        <div id="result" class="result" role="status" aria-live="polite" hidden></div>
    </section>
@endsection

@section('scripts')
    <style>
        .typing-stage {
            position: relative;
            padding: 22px 24px;
            overflow: hidden !important;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #101114;
            cursor: text;
        }
        .inline-typing-text {
            font: clamp(17px, 2.2vw, 25px) / 1.9 'Space Mono', monospace;
            color: #777a82;
            height: 5.7em;
            max-height: 5.7em;
            min-height: 5.7em;
            overflow: hidden !important;
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            user-select: none;
        }
        .typing-capture {
            position: absolute;
            width: 1px;
            height: 1px;
            min-height: 1px;
            padding: 0;
            margin: 0;
            border: 0;
            opacity: 0;
            pointer-events: none;
            resize: none;
        }
        .typing-char { position: relative; color: #858891; transition: color 70ms ease; }
        .typing-char.is-correct { color: #6cceb0; }
        .typing-char.is-wrong { color: #fa7683; background: rgba(250, 118, 131, 0.08); border-radius: 2px; }
        .typing-char.is-wrong.is-space { background: rgba(250, 118, 131, 0.32); }
        .typing-char.is-current::before {
            position: absolute; top: 10%; bottom: 7%; left: -2px; width: 2px; border-radius: 2px;
            background: #f2f2f2; content: ''; animation: typing-caret 1s steps(1, end) infinite;
        }
        @keyframes typing-caret { 50% { opacity: 0; } }
    </style>
    <script src="{{ asset('js/inline-typing.js') }}?v=4" defer></script>
    <script src="{{ asset('js/typing.js') }}?v=4" defer></script>
@endsection
