<?php

namespace App\Http\Controllers;

use App\Ai\Agents\KpiAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * The chat bubble's endpoint: one question in, one answer out.
 *
 * The conversation is kept in the session (last few turns only), so a
 * follow-up like "and last year?" makes sense, and it never touches the
 * database. Every signed-in user can use it; what each may see is enforced
 * by the assistant's tools.
 */
class AssistantController extends Controller
{
    private const SESSION_KEY = 'assistant.history';

    public function ask(Request $request): JsonResponse
    {
        abort_unless(config('assistant.enabled'), 404);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1000'],
        ]);

        $question = trim($validated['message']);
        $history = array_slice((array) $request->session()->get(self::SESSION_KEY, []), -config('assistant.history', 8));

        try {
            $response = (new KpiAssistant($request->user(), $history))->prompt(
                $question,
                provider: config('assistant.provider'),
                model: config('assistant.model'),
                timeout: config('assistant.timeout'),
            );
            $answer = trim((string) $response);
        } catch (Throwable $e) {
            Log::warning('KPI Assistant failed', ['error' => $e->getMessage()]);

            return response()->json([
                'error' => $this->friendlyError($e),
            ], 503);
        }

        if ($answer === '') {
            $answer = "Sorry, I couldn't find an answer to that. Try asking in a different way.";
        }

        $history[] = ['role' => 'user', 'content' => $question];
        $history[] = ['role' => 'assistant', 'content' => $answer];
        $request->session()->put(self::SESSION_KEY, array_slice($history, -config('assistant.history', 8)));

        return response()->json(['answer' => $answer]);
    }

    /**
     * Starts a new conversation.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return response()->json(['ok' => true]);
    }

    private function friendlyError(Throwable $e): string
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'connection refused') || str_contains($message, 'could not connect') || str_contains($message, 'failed to connect') || str_contains($message, 'curl error 7')) {
            return 'The assistant is offline: the local AI (Ollama) is not running. Start Ollama on the server and try again.';
        }

        if (str_contains($message, 'timed out') || str_contains($message, 'timeout') || str_contains($message, 'curl error 28')) {
            return 'That took too long to answer. Try a shorter or more specific question.';
        }

        if (str_contains($message, 'not found') && str_contains($message, 'model')) {
            return 'The AI model is not installed. On the server run: ollama pull '.config('assistant.model');
        }

        return 'Sorry, the assistant could not answer right now. Please try again.';
    }
}
