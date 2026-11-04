/**
 * Inline character feedback shared by Typing Test and Friendly Duel.
 *
 * The real keyboard focus stays on an accessible, visually hidden textarea.
 * This module only paints the reference text, so no HTML from the database is
 * inserted directly into the page.
 */
(() => {
    'use strict';

    function createTypingDisplay(container) {
        let chars = [];
        let original = [];
        let previousInput = '';

        function setText(text) {
            const source = String(text || '');
            const fragment = document.createDocumentFragment();
            const segments = source.match(/\S+\s*|\s+/gu) || [];

            chars = [];
            original = Array.from(source);
            previousInput = '';

            for (const segment of segments) {
                const word = document.createElement('span');
                word.className = 'typing-word';

                for (const character of Array.from(segment)) {
                    const span = document.createElement('span');
                    span.className = 'typing-char';
                    span.textContent = character;

                    if (/\s/u.test(character)) {
                        span.classList.add('is-space');
                    }

                    chars.push(span);
                    word.appendChild(span);
                }

                fragment.appendChild(word);
            }

            container.replaceChildren(fragment);
            container.scrollTop = 0;
            update('');
        }

        function update(value) {
            const typed = Array.from(String(value || ''));
            const before = Array.from(previousInput);
            const end = Math.max(typed.length, before.length);

            for (let index = 0; index <= end && index < chars.length; index++) {
                if (
                    before[index] === typed[index] &&
                    index !== before.length &&
                    index !== typed.length
                ) {
                    continue;
                }

                const span = chars[index];
                span.classList.remove('is-correct', 'is-wrong', 'is-current');

                if (index < typed.length) {
                    span.classList.add(
                        typed[index] === original[index] ? 'is-correct' : 'is-wrong'
                    );
                } else if (index === typed.length) {
                    span.classList.add('is-current');
                }
            }

            previousInput = String(value || '');

            // Show only three rows. Move the invisible scroll position by one
            // complete line when the caret reaches the next row, so there is
            // no scrollbar and no partially clipped sentence on screen.
            const current = chars[Math.min(typed.length, chars.length - 1)];
            if (current && container.clientHeight) {
                const lineHeight = parseFloat(getComputedStyle(container).lineHeight);
                const top = current.getBoundingClientRect().top
                    - container.getBoundingClientRect().top + container.scrollTop;
                const currentRow = Math.floor((top + 1) / lineHeight);
                const firstVisibleRow = Math.round(container.scrollTop / lineHeight);

                if (currentRow > firstVisibleRow + 2) {
                    container.scrollTop = Math.max(0, (currentRow - 2) * lineHeight);
                } else if (currentRow < firstVisibleRow) {
                    container.scrollTop = Math.max(0, currentRow * lineHeight);
                }
            }
        }

        return {
            setText,
            update,
            length: () => original.length,
        };
    }

    window.TypewudInlineTyping = { createTypingDisplay };
})();
