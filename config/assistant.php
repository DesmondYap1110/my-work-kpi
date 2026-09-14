<?php

/*
|--------------------------------------------------------------------------
| KPI Assistant
|--------------------------------------------------------------------------
|
| The in-app chat assistant (the chat bubble on every page). It runs on a
| local model through Ollama by default, so no API key is needed and no data
| leaves this machine. See app/Ai/Agents/KpiAssistant.php.
|
| To use it: install Ollama, then `ollama pull qwen2.5:7b`. Ollama must be
| running on the same machine as the app (or set ASSISTANT_OLLAMA_URL).
|
*/

return [

    // Turn the chat bubble off entirely.
    'enabled' => env('ASSISTANT_ENABLED', true),

    // Any provider laravel/ai supports; 'ollama' is local and needs no key.
    'provider' => env('ASSISTANT_PROVIDER', 'ollama'),

    // qwen2.5:7b is reliable at calling the database tools on a laptop GPU.
    'model' => env('ASSISTANT_MODEL', 'qwen2.5:7b'),

    // Seconds to wait for an answer. A local model on a laptop can take a
    // minute when it has to look several things up.
    'timeout' => (int) env('ASSISTANT_TIMEOUT', 180),

    // Previous messages sent with each question, so follow-ups like "and
    // last year?" make sense. Kept small: a local model slows with context.
    'history' => 8,

    // Rows a lookup may return, so one question cannot pull the whole table.
    'row_limit' => 15,

];
