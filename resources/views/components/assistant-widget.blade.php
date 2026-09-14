{{--
    KPI Assistant chat bubble, bottom-right on every signed-in page.

    Talks to AssistantController (POST /assistant), which runs the local AI.
    Suggestions differ by role because members can only ask about themselves.
    Behaviour: public/js/modules/assistant.js. Off when assistant.enabled is false.
--}}
@auth
    @if (config('assistant.enabled'))
        @php
            $suggestions = auth()->user()->isAdmin()
                ? ['Who has the highest KPI score this year?', 'Which appraisals are overdue or due soon?', 'How do I set up KPI for a position?', 'How is the KPI score calculated?']
                : ['What is my KPI score this year?', 'Show my open tasks', 'When is my next appraisal?', 'How do I fill in my self-assessment?'];
        @endphp
        <div class="assistant no-print" data-assistant
             data-ask-url="{{ route('assistant.ask') }}"
             data-reset-url="{{ route('assistant.reset') }}">
            <button type="button" class="assistant-toggle" data-assistant-toggle aria-expanded="false" aria-controls="assistant-panel" title="Ask the KPI Assistant">
                <i class="ri-robot-line assistant-toggle-open"></i>
                <i class="ri-close-line assistant-toggle-close"></i>
                <span class="visually-hidden">KPI Assistant</span>
            </button>

            <section class="assistant-panel" id="assistant-panel" data-assistant-panel hidden aria-label="KPI Assistant">
                <header class="assistant-head">
                    <span class="assistant-avatar"><i class="ri-robot-line"></i></span>
                    <div class="assistant-head-text">
                        <strong>KPI Assistant</strong>
                        <small>Runs on this server's local AI &middot; read-only</small>
                    </div>
                    <button type="button" class="assistant-icon-btn" data-assistant-reset title="New conversation" aria-label="New conversation">
                        <i class="ri-refresh-line"></i>
                    </button>
                </header>

                <div class="assistant-messages" data-assistant-messages aria-live="polite">
                    <div class="assistant-msg is-bot">
                        <p>Hi {{ Str::before(auth()->user()->staff_name, ' ') }}! Ask me about
                            {{ auth()->user()->isAdmin() ? 'KPI scores, teams, tasks, appraisals' : 'your KPI score, tasks and appraisals' }},
                            or how to use the app.</p>
                    </div>
                    <div class="assistant-suggestions" data-assistant-suggestions>
                        @foreach ($suggestions as $suggestion)
                            <button type="button" class="assistant-chip" data-assistant-suggestion>{{ $suggestion }}</button>
                        @endforeach
                    </div>
                </div>

                <form class="assistant-form" data-assistant-form>
                    <textarea class="assistant-input" data-assistant-input rows="1" maxlength="1000"
                              placeholder="Ask a question..." aria-label="Your question"></textarea>
                    <button type="submit" class="assistant-send" data-assistant-send aria-label="Send">
                        <i class="ri-send-plane-2-fill"></i>
                    </button>
                </form>
            </section>
        </div>
    @endif
@endauth
