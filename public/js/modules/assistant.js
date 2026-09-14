/**
 * KPI Assistant chat bubble - resources/views/components/assistant-widget.blade.php.
 *
 * Posts each question to AssistantController and shows the answer. Answers
 * come back as plain text with a little markdown (**bold**, "- " lists,
 * numbered lists); it is escaped first and only those few patterns are turned
 * into HTML, so nothing in an answer can inject markup.
 *
 * The local AI can take a while, so a "thinking" bubble shows elapsed seconds.
 */
App.module('assistant', function () {
    'use strict';

    var root = document.querySelector('[data-assistant]');

    if (!root) {
        return;
    }

    var toggle = root.querySelector('[data-assistant-toggle]');
    var panel = root.querySelector('[data-assistant-panel]');
    var messages = root.querySelector('[data-assistant-messages]');
    var form = root.querySelector('[data-assistant-form]');
    var input = root.querySelector('[data-assistant-input]');
    var send = root.querySelector('[data-assistant-send]');
    var suggestions = root.querySelector('[data-assistant-suggestions]');
    var token = document.querySelector('meta[name="csrf-token"]');
    var busy = false;
    var STORAGE = 'kpiAssistantLog';

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Escaped text -> a few safe tags: paragraphs, bullet / numbered lists, bold.
    function format(text) {
        var html = '';
        var list = null;

        escapeHtml(text).split(/\n/).forEach(function (raw) {
            var line = raw.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            var bullet = line.match(/^\s*[-*•]\s+(.*)$/);
            var numbered = line.match(/^\s*\d+[.)]\s+(.*)$/);

            if (bullet || numbered) {
                var type = bullet ? 'ul' : 'ol';
                if (list !== type) {
                    if (list) html += '</' + list + '>';
                    html += '<' + type + '>';
                    list = type;
                }
                html += '<li>' + (bullet || numbered)[1] + '</li>';
                return;
            }

            if (list) {
                html += '</' + list + '>';
                list = null;
            }

            if (line.trim() !== '') {
                html += '<p>' + line + '</p>';
            }
        });

        return html + (list ? '</' + list + '>' : '');
    }

    function scrollDown() {
        messages.scrollTop = messages.scrollHeight;
    }

    function addMessage(role, text, remember) {
        var bubble = document.createElement('div');
        bubble.className = 'assistant-msg ' + (role === 'user' ? 'is-user' : role === 'error' ? 'is-error' : 'is-bot');
        bubble.innerHTML = role === 'user' ? '<p>' + escapeHtml(text) + '</p>' : format(text);
        messages.appendChild(bubble);
        scrollDown();

        if (remember) {
            try {
                var log = JSON.parse(sessionStorage.getItem(STORAGE) || '[]');
                log.push({ role: role, text: text });
                sessionStorage.setItem(STORAGE, JSON.stringify(log.slice(-20)));
            } catch (e) { /* storage unavailable - the chat still works */ }
        }
    }

    function thinking() {
        var bubble = document.createElement('div');
        bubble.className = 'assistant-msg is-bot is-thinking';
        bubble.innerHTML = '<span class="assistant-dots"><i></i><i></i><i></i></span> <small>Thinking… <span data-seconds>0</span>s</small>';
        messages.appendChild(bubble);
        scrollDown();

        var started = Date.now();
        var timer = setInterval(function () {
            bubble.querySelector('[data-seconds]').textContent = Math.round((Date.now() - started) / 1000);
        }, 1000);

        return function () {
            clearInterval(timer);
            bubble.remove();
        };
    }

    function setBusy(state) {
        busy = state;
        send.disabled = state;
        input.disabled = state;
        root.classList.toggle('is-busy', state);
    }

    function ask(question) {
        question = question.trim();

        if (!question || busy) {
            return;
        }

        if (suggestions) {
            suggestions.hidden = true;
        }

        addMessage('user', question, true);
        input.value = '';
        autosize();
        setBusy(true);
        var stop = thinking();

        fetch(root.getAttribute('data-ask-url'), {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
            },
            body: JSON.stringify({ message: question }),
        })
            .then(function (response) {
                return response.json().catch(function () { return {}; }).then(function (data) {
                    return { status: response.status, data: data };
                });
            })
            .then(function (result) {
                stop();
                if (result.status === 429) {
                    addMessage('error', 'Too many questions in a minute. Please wait a moment and try again.', false);
                } else if (result.data.answer) {
                    addMessage('bot', result.data.answer, true);
                } else {
                    var error = result.data.error || (result.data.errors && result.data.errors.message && result.data.errors.message[0]) || 'Sorry, something went wrong. Please try again.';
                    addMessage('error', error, false);
                }
            })
            .catch(function () {
                stop();
                addMessage('error', 'Could not reach the server. Check your connection and try again.', false);
            })
            .finally(function () {
                setBusy(false);
                input.focus();
            });
    }

    function autosize() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';
    }

    function open(state) {
        panel.hidden = !state;
        root.classList.toggle('is-open', state);
        toggle.setAttribute('aria-expanded', String(state));
        if (state) {
            scrollDown();
            input.focus();
        }
    }

    toggle.addEventListener('click', function () {
        open(panel.hidden);
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        ask(input.value);
    });

    input.addEventListener('keydown', function (event) {
        // Enter sends; Shift+Enter makes a new line.
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            ask(input.value);
        }
    });

    input.addEventListener('input', autosize);

    root.addEventListener('click', function (event) {
        var chip = event.target.closest('[data-assistant-suggestion]');
        if (chip) {
            ask(chip.textContent);
        }
    });

    root.querySelector('[data-assistant-reset]').addEventListener('click', function () {
        fetch(root.getAttribute('data-reset-url'), {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token ? token.getAttribute('content') : '' },
        });
        try { sessionStorage.removeItem(STORAGE); } catch (e) { /* ignore */ }
        messages.querySelectorAll('.assistant-msg:not(:first-child)').forEach(function (el) { el.remove(); });
        if (suggestions) {
            suggestions.hidden = false;
        }
        input.focus();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !panel.hidden) {
            open(false);
            toggle.focus();
        }
    });

    // The conversation survives moving between pages in this tab.
    try {
        var log = JSON.parse(sessionStorage.getItem(STORAGE) || '[]');
        if (log.length && suggestions) {
            suggestions.hidden = true;
        }
        log.forEach(function (entry) {
            addMessage(entry.role, entry.text, false);
        });
    } catch (e) { /* ignore */ }
});
