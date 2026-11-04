/**
 * Typewud typing engine (no frontend build tools required).
 * The typed text is captured by a visually hidden textarea. The visible text
 * itself receives character-level feedback from inline-typing.js.
 */
(() => {
    'use strict';

    const element = (id) => document.getElementById(id);
    const mode = element('mode');
    const difficulty = element('difficulty');
    const language = element('language');
    const target = element('target');
    const targetLabel = element('targetLabel');
    const stage = element('typingStage');
    const input = element('typed');
    const result = element('result');
    const restart = element('restart');
    const pause = element('pause');

    const display = window.TypewudInlineTyping.createTypingDisplay(element('prompt'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const authenticated = element('typingApp').dataset.authenticated === '1';

    let currentContent = null;
    let startedAt = null;
    let interval = null;
    let completed = false;
    let paused = false;
    let pausedAt = 0;
    let pauseDuration = 0;
    let requestVersion = 0;

    const timeOptions = [15, 30, 60, 120];
    const wordOptions = [10, 25, 50, 100];

    function letters(text) {
        return Array.from(text);
    }

    function correctCharacters(text, reference) {
        const actual = letters(text);
        const expected = letters(reference);
        let count = 0;

        actual.forEach((letter, index) => {
            if (letter === expected[index]) {
                count++;
            }
        });

        return count;
    }

    function elapsedMilliseconds() {
        if (startedAt === null) {
            return 0;
        }

        const currentTime = paused ? pausedAt : performance.now();
        return Math.max(0, currentTime - startedAt - pauseDuration);
    }

    function setFocus() {
        if (!input.disabled) {
            input.focus({ preventScroll: true });
            stage.classList.add('is-focused');
        }
    }

    function updateMetrics() {
        if (!currentContent) {
            return;
        }

        const elapsedSeconds = elapsedMilliseconds() / 1000;
        const actual = input.value;
        const correct = correctCharacters(actual, currentContent.body);
        const accuracy = actual.length ? Math.round(100 * correct / actual.length) : 100;
        const wpm = elapsedSeconds > 0
            ? Math.round(correct / 5 / (elapsedSeconds / 60))
            : 0;

        element('wpm').textContent = String(wpm);
        element('accuracy').textContent = `${accuracy}%`;

        if (mode.value === 'time') {
            element('timer').textContent = String(
                Math.max(0, Math.ceil(Number(target.value) - elapsedSeconds))
            );

            if (startedAt !== null && elapsedSeconds >= Number(target.value)) {
                finish();
            }
        } else {
            element('timer').textContent = String(Math.floor(elapsedSeconds));
        }
    }

    function setTargetOptions() {
        const previous = Number(target.value);
        const options = mode.value === 'words' ? wordOptions : timeOptions;

        target.replaceChildren();

        options.forEach((value) => {
            const option = document.createElement('option');
            option.value = String(value);
            option.textContent = mode.value === 'words' ? `${value} words` : `${value}s`;
            target.appendChild(option);
        });

        target.value = String(options.includes(previous) ? previous : options[2]);
        targetLabel.firstChild.textContent = mode.value === 'words' ? 'Word target' : 'Duration';
        targetLabel.hidden = mode.value === 'practice';
    }

    function resetState() {
        window.clearInterval(interval);
        interval = null;
        startedAt = null;
        completed = false;
        paused = false;
        pausedAt = 0;
        pauseDuration = 0;
        currentContent = null;
        input.value = '';
        input.disabled = true;
        result.hidden = true;
        result.textContent = '';
        stage.classList.remove('is-focused');
        display.setText('');

        element('wpm').textContent = '0';
        element('accuracy').textContent = '100%';
        element('timer').textContent = mode.value === 'time' ? target.value : '0';

        pause.hidden = mode.value !== 'practice';
        pause.textContent = 'Pause practice';
    }

    async function loadText() {
        const version = ++requestVersion;
        resetState();
        element('hint').textContent = 'Memuat teks...';

        const params = new URLSearchParams({
            mode: mode.value,
            difficulty: difficulty.value,
            language: language.value,
            target: target.value,
        });

        try {
            const response = await fetch(`/api/typing-content?${params}`, {
                headers: { Accept: 'application/json' },
            });
            const data = await response.json();

            // Ignore responses belonging to a previous configuration.
            if (version !== requestVersion) {
                return;
            }

            if (!response.ok) {
                throw new Error(data.message || 'Teks latihan tidak tersedia.');
            }

            currentContent = data;
            input.maxLength = display.length() || 5000;
            display.setText(data.body);
            input.maxLength = display.length();
            input.disabled = false;
            element('hint').textContent = 'Tiga baris ditampilkan sekaligus. Teks bergeser saat kamu mengetik.';
            setFocus();
        } catch (error) {
            if (version === requestVersion) {
                element('hint').textContent = error.message;
            }
        }
    }

    async function finish() {
        if (completed || !currentContent) {
            return;
        }

        completed = true;
        window.clearInterval(interval);
        interval = null;
        input.disabled = true;
        stage.classList.remove('is-focused');

        const duration = Math.max(1000, Math.round(elapsedMilliseconds()));
        const actual = input.value;
        const correct = correctCharacters(actual, currentContent.body);
        const accuracy = actual.length ? Math.round(100 * correct / actual.length) : 100;
        const wpm = Math.round(correct / 5 / (duration / 60000));

        result.hidden = false;
        result.textContent = `Selesai · ${wpm} WPM · ${accuracy}% accuracy · ` +
            `${actual.length - correct} kesalahan.`;

        if (!authenticated) {
            result.textContent += ' Login untuk menyimpan hasil.';
            return;
        }

        // A result is only submitted for signed-in users. Practice is saved
        // to history but never enters the competitive leaderboard.
        try {
            const response = await fetch('/api/typing-results', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                body: JSON.stringify({
                    typing_content_id: currentContent.id,
                    prompt_seed: currentContent.prompt_seed,
                    prompt_issued_at: currentContent.prompt_issued_at,
                    prompt_signature: currentContent.prompt_signature,
                    mode: mode.value,
                    difficulty: difficulty.value,
                    language: language.value,
                    target: Number(target.value),
                    elapsed_ms: duration,
                    typed: actual,
                    input_method: matchMedia('(pointer:coarse)').matches ? 'touch' : 'physical',
                }),
            });
            const data = await response.json();

            result.textContent += response.ok
                ? ' Hasil tersimpan di dashboard.'
                : ` Gagal menyimpan: ${data.message || 'validasi gagal'}.`;
        } catch (error) {
            result.textContent += ' Gagal menghubungi server untuk menyimpan hasil.';
        }
    }

    function handleTyping() {
        if (completed || paused || !currentContent) {
            return;
        }

        const typed = letters(input.value);
        if (typed.length > display.length()) {
            input.value = typed.slice(0, display.length()).join('');
        }

        if (startedAt === null && input.value.length > 0) {
            startedAt = performance.now();
            interval = window.setInterval(updateMetrics, 100);
        }

        display.update(input.value);
        updateMetrics();

        if (
            mode.value !== 'time' &&
            letters(input.value).length >= display.length() &&
            display.length() > 0
        ) {
            finish();
        }
    }

    function togglePause() {
        if (mode.value !== 'practice' || completed || startedAt === null) {
            return;
        }

        paused = !paused;

        if (paused) {
            pausedAt = performance.now();
            input.disabled = true;
            pause.textContent = 'Resume practice';
            stage.classList.remove('is-focused');
        } else {
            pauseDuration += performance.now() - pausedAt;
            input.disabled = false;
            pause.textContent = 'Pause practice';
            setFocus();
        }
    }

    // Clicking the reference text focuses the invisible keyboard capture.
    stage.addEventListener('click', setFocus);
    stage.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') {
            setFocus();
        }
    });
    input.addEventListener('focus', () => stage.classList.add('is-focused'));
    input.addEventListener('blur', () => stage.classList.remove('is-focused'));
    input.addEventListener('input', handleTyping);
    input.addEventListener('paste', (event) => event.preventDefault());
    input.addEventListener('drop', (event) => event.preventDefault());
    restart.addEventListener('click', loadText);
    pause.addEventListener('click', togglePause);

    mode.addEventListener('change', () => {
        setTargetOptions();
        loadText();
    });
    [difficulty, language, target].forEach((select) => {
        select.addEventListener('change', loadText);
    });

    setTargetOptions();
    loadText();
})();
