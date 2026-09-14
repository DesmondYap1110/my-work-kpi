<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AppGuide;
use App\Ai\Tools\FindMembers;
use App\Ai\Tools\MemberAppraisals;
use App\Ai\Tools\MemberKpiScore;
use App\Ai\Tools\MemberTasks;
use App\Ai\Tools\ProjectOverview;
use App\Ai\Tools\TeamPerformance;
use App\Models\Staff;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

/**
 * The in-app KPI Assistant: answers questions about KPI scores, tasks,
 * appraisals and how to use the app, by calling read-only lookups.
 *
 * It is built for one person per request. What they may see is enforced by
 * the tools (App\Ai\Tools\AssistantTool), so nothing typed into the chat can
 * widen it. Which model runs it is config/assistant.php.
 */
#[MaxSteps(6)]
#[Temperature(0.2)]
class KpiAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    /**
     * @param  array<int, array{role: string, content: string}>  $history  earlier turns, oldest first
     */
    public function __construct(
        private readonly Staff $user,
        private readonly array $history = [],
    ) {
    }

    public function instructions(): string
    {
        $user = $this->user->loadMissing(['position', 'team']);
        $role = $user->isAdmin()
            ? 'the administrator. They may ask about any member, team, project, appraisal or the company report. '
                .'Their menu: Dashboard, Human Resource (Team, Position, Member), Project Setup (Project, Task), Appraisal (Review, Schedule), KPI Report, Settings (Project Tag Setting, Project Form Setup, Theme Setting, Change Password).'
            : 'a member ('.($user->position->position_name ?? 'no position').', '.($user->team->team_name ?? 'no team').'). '
                .'They may ask about their own KPI, tasks, projects and appraisals, and how to use the app - never about other members. '
                .'Their menu has only: Dashboard, My KPI, My Tasks, My Appraisal, Project, Settings (Change Password). Only point them to those - they do not have KPI Report, Human Resource or Appraisal > Review.';

        return <<<PROMPT
        You are the MyKPI Assistant inside a company KPI and appraisal web app.
        Today is {$this->today()}. You are talking to {$user->staff_name}, {$role}

        How to answer:
        - For any fact about people, scores, tasks, projects or appraisals, call a tool. Never invent or estimate numbers, names or dates.
        - For "how do I…", "where is…", "what does… mean" or "how is the score calculated" questions, call app_guide with the closest topic and answer from it.
        - "My", "me" or no name means the person asking: leave the member field empty.
        - If a tool says "Not allowed", explain politely that they can only see their own information.
        - If a tool finds nothing, say so plainly and suggest what to check.
        - Periods: default to this year unless the question says otherwise (this month, last month, last year, or dates).
        - Reply in the same language the user writes in. Keep answers short: a sentence or two, then a bulleted list when there are several items. Use **bold** for key numbers.
        - Give scores as "72.5 / 100". Refer to screens by their menu names (e.g. KPI Report, Appraisal > Schedule).
        - You can only read information. If asked to change, add or delete something, explain where in the app they can do it.
        PROMPT;
    }

    /**
     * @return Message[]
     */
    public function messages(): iterable
    {
        return array_map(fn (array $turn) => new Message($turn['role'], $turn['content']), $this->history);
    }

    public function tools(): iterable
    {
        $tools = [
            new AppGuide($this->user),
            new MemberKpiScore($this->user),
            new MemberTasks($this->user),
            new MemberAppraisals($this->user),
            new ProjectOverview($this->user),
            new FindMembers($this->user),
        ];

        // Offered only to the administrator, so the model is not even tempted.
        if ($this->user->isAdmin()) {
            $tools[] = new TeamPerformance($this->user);
        }

        return $tools;
    }

    private function today(): string
    {
        return now()->format('l, j F Y');
    }
}
