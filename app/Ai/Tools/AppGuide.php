<?php

namespace App\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Ai\Tools\Request;

/**
 * How to use the app - the written guide in resources/ai/guide.md, one
 * section per topic. The model answers "how do I…" questions from this rather
 * than from what it guesses the app does.
 */
class AppGuide extends AssistantTool
{
    public function description(): string
    {
        return 'Get the official step-by-step guide for using this KPI app. Use for any "how do I", "where is", '
            .'"what does X mean" or "how is the score calculated" question. Topics: '.implode(', ', array_keys($this->sections())).'.';
    }

    public function handle(Request $request): string
    {
        $sections = $this->sections();
        $topic = Str::slug((string) ($request['topic'] ?? ''));

        if (isset($sections[$topic])) {
            return $sections[$topic];
        }

        // A near match ("kpi" -> "kpi-setting") rather than nothing.
        foreach ($sections as $key => $text) {
            if ($topic !== '' && (str_contains($key, $topic) || str_contains($topic, $key))) {
                return $text;
            }
        }

        return "Topics available: ".implode(', ', array_keys($sections)).".\n\n".$sections['overview'];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'topic' => $schema->string()->enum(array_keys($this->sections()))->required()->description('The guide section to read.'),
        ];
    }

    /**
     * The guide split on its "## topic-key" headings.
     *
     * @return array<string, string>
     */
    private function sections(): array
    {
        static $sections = null;

        if ($sections !== null) {
            return $sections;
        }

        $sections = [];
        $current = null;

        foreach (preg_split('/\R/', (string) @file_get_contents(resource_path('ai/guide.md'))) as $line) {
            if (preg_match('/^##\s+([a-z0-9-]+)\s*$/', $line, $m)) {
                $current = $m[1];
                $sections[$current] = '';
            } elseif ($current !== null) {
                $sections[$current] .= $line."\n";
            }
        }

        return $sections = array_map('trim', $sections);
    }
}
