/** Friendly Duel with AJAX polling. Uses the same inline feedback as solo tests. */
(() => {
    'use strict';

    const config = window.TYPEWUD_DUEL;
    const element = (id) => document.getElementById(id);
    const input = element('duelInput');
    const stage = element('duelStage');
    const ready = element('ready');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    const display = window.TypewudInlineTyping.createTypingDisplay(element('duelPrompt'));

    display.setText(config.text);
    input.maxLength = display.length();

    let started = false;
    let submitted = false;
    let posting = false;
    let stateBusy = false;
    let lastProgressAt = 0;

    async function post(path, data) {
        return fetch(`/duel/${config.code}/${path}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                Accept: 'application/json',
            },
            body: JSON.stringify(data),
        });
    }

    function focusInput() {
        if (!input.disabled) {
            input.focus({ preventScroll: true });
            stage.classList.add('is-focused');
        }
    }

    function disableInput() {
        input.disabled = true;
        stage.classList.remove('is-focused');
    }

    function showResults(state) {
        const host = state.host_result;
        const guest = state.guest_result;
        const panel = element('duelResult');
        panel.hidden = false;

        if (!host || !guest) {
            panel.textContent = 'Duel selesai. Menunggu hasil akhir pemain lain.';
            return;
        }

        let winner = 'DRAW';
        if (host.wpm !== guest.wpm) {
            winner = host.wpm > guest.wpm ? state.host : state.guest;
        } else if (host.accuracy !== guest.accuracy) {
            winner = host.accuracy > guest.accuracy ? state.host : state.guest;
        } else if (host.correct_chars !== guest.correct_chars) {
            winner = host.correct_chars > guest.correct_chars ? state.host : state.guest;
        }

        const verdict = winner === 'DRAW' ? 'Draw' : `Winner: ${winner}`;
        panel.textContent = `${verdict} · ${state.host}: ${host.wpm} WPM ` +
            `(${host.accuracy}%) · ${state.guest}: ${guest.wpm} WPM ` +
            `(${guest.accuracy}%)`;
    }

    async function submitFinish() {
        if (submitted || posting) {
            return;
        }

        posting = true;
        disableInput();

        try {
            const response = await post('finish', { typed: input.value });
            if (response.ok) {
                submitted = true;
            }
        } catch (error) {
            // Retry on the next polling cycle after a network interruption.
        } finally {
            posting = false;
        }
    }

    async function updateState() {
        if (stateBusy) {
            return;
        }

        stateBusy = true;

        try {
            const response = await fetch(`/duel/${config.code}/state`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                return;
            }

            const state = await response.json();
            element('guestName').textContent = state.guest || 'Waiting...';
            element('hostState').textContent = state.host_ready ? 'READY' : 'WAITING';
            element('guestState').textContent = state.guest_ready ? 'READY' : 'WAITING';
            element('hostBar').style.width = `${Math.min(100, state.host_progress / display.length() * 100)}%`;
            element('guestBar').style.width = `${Math.min(100, state.guest_progress / display.length() * 100)}%`;

            if (state.status === 'waiting') {
                element('clock').textContent = 'WAITING';
                return;
            }

            const beginning = Date.parse(state.starts_at);
            const ending = Date.parse(state.ends_at);
            const now = Date.now();

            if (state.status === 'playing' && now < beginning) {
                element('clock').textContent = `START IN ${Math.ceil((beginning - now) / 1000)}`;
                disableInput();
                return;
            }

            if (state.status === 'playing' && now < ending) {
                element('clock').textContent = `${Math.ceil((ending - now) / 1000)}s`;

                if (!started && !submitted) {
                    started = true;
                    input.disabled = false;
                    focusInput();
                }

                return;
            }

            element('clock').textContent = 'FINISHED';
            disableInput();
            await submitFinish();
            showResults(state);
        } catch (error) {
            element('clock').textContent = 'RECONNECTING';
        } finally {
            stateBusy = false;
        }
    }

    ready.addEventListener('click', async () => {
        ready.disabled = true;

        try {
            const response = await post('ready', {});
            if (!response.ok) {
                throw new Error('Ready gagal.');
            }

            ready.textContent = 'Ready ✓';
        } catch (error) {
            ready.disabled = false;
            ready.textContent = 'Try Ready Again';
        }
    });

    input.addEventListener('input', async () => {
        if (!started || submitted) {
            return;
        }

        const value = Array.from(input.value).slice(0, display.length()).join('');
        if (input.value !== value) {
            input.value = value;
        }

        display.update(input.value);

        const now = Date.now();
        if (now - lastProgressAt < 750) {
            return;
        }

        lastProgressAt = now;

        try {
            await post('progress', { typed: input.value });
        } catch (error) {
            // The next keystroke or poll can retry.
        }
    });

    input.addEventListener('paste', (event) => event.preventDefault());
    input.addEventListener('drop', (event) => event.preventDefault());
    input.addEventListener('focus', () => stage.classList.add('is-focused'));
    input.addEventListener('blur', () => stage.classList.remove('is-focused'));
    stage.addEventListener('click', focusInput);
    stage.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') {
            focusInput();
        }
    });

    window.setInterval(updateState, 1000);
    updateState();
})();
