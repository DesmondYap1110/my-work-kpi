<?php

namespace Tests\Feature;

use App\Ai\Agents\KpiAssistant;
use App\Ai\Tools\AppGuide;
use App\Ai\Tools\FindMembers;
use App\Ai\Tools\MemberKpiScore;
use App\Ai\Tools\TeamPerformance;
use App\Models\Staff;
use App\Models\StaffPosition;
use Laravel\Ai\Tools\Request;
use Tests\TestCase;

/**
 * The KPI Assistant's guard rails. What a member may see is enforced by the
 * tools, not by the model's instructions - these tests hold that line without
 * running any AI model.
 */
class KpiAssistantTest extends TestCase
{
    private function member(int $id = 900001): Staff
    {
        return (new Staff)->forceFill(['id' => $id, 'staff_name' => 'Test Member', 'position_id' => 999, 'is_active' => true]);
    }

    private function admin(): Staff
    {
        return (new Staff)->forceFill(['id' => 900000, 'staff_name' => 'Admin', 'position_id' => StaffPosition::ADMIN_ID]);
    }

    public function test_team_performance_is_refused_to_a_member(): void
    {
        $answer = (new TeamPerformance($this->member()))->handle(new Request([]));

        $this->assertStringStartsWith('Not allowed', $answer);
    }

    public function test_team_performance_is_not_even_offered_to_a_member(): void
    {
        $names = collect((new KpiAssistant($this->member()))->tools())->map(fn ($t) => $t->name());

        $this->assertNotContains('team_performance', $names);
        $this->assertContains('member_kpi_score', $names);
        $this->assertContains('team_performance', collect((new KpiAssistant($this->admin()))->tools())->map(fn ($t) => $t->name()));
    }

    public function test_a_member_asking_about_someone_else_is_refused(): void
    {
        // A name that is not them - no lookup of anyone else's score happens.
        $answer = (new MemberKpiScore($this->member()))->handle(new Request(['member' => 'zz-nobody-by-this-name']));

        $this->assertStringStartsWith('Not allowed', $answer);
    }

    public function test_a_member_searching_members_only_ever_sees_themselves(): void
    {
        $result = json_decode((new FindMembers($this->member()))->handle(new Request(['name' => 'a'])), true);

        $this->assertCount(1, $result['members']);
        $this->assertSame('Test Member', $result['members'][0]['name']);
    }

    public function test_the_guide_has_every_topic_the_assistant_offers(): void
    {
        $guide = new AppGuide($this->member());

        foreach (['overview', 'kpi-setting', 'project-tags', 'kpi-score', 'appraisal', 'self-assessment', 'review-schedule', 'kpi-report'] as $topic) {
            $text = $guide->handle(new Request(['topic' => $topic]));
            $this->assertStringNotContainsString('Topics available', $text, "Guide topic '{$topic}' is missing from resources/ai/guide.md.");
        }
    }

    public function test_tools_have_snake_case_names(): void
    {
        $this->assertSame('member_kpi_score', (new MemberKpiScore($this->member()))->name());
        $this->assertSame('app_guide', (new AppGuide($this->member()))->name());
    }

    public function test_the_chat_endpoint_returns_the_answer_and_keeps_history(): void
    {
        KpiAssistant::fake(['Your KPI score is **80 / 100**.']);

        $member = Staff::query()->excludingAdmin()->first();

        if (! $member) {
            $this->markTestSkipped('No member in the database to sign in as.');
        }

        $this->actingAs($member)
            ->postJson(route('assistant.ask'), ['message' => 'What is my KPI score?'])
            ->assertOk()
            ->assertJson(['answer' => 'Your KPI score is **80 / 100**.'])
            ->assertSessionHas('assistant.history', fn ($history) => count($history) === 2);

        $this->actingAs($member)->deleteJson(route('assistant.reset'))->assertOk()->assertSessionMissing('assistant.history');
    }

    public function test_a_guest_cannot_use_the_assistant(): void
    {
        $this->postJson(route('assistant.ask'), ['message' => 'hi'])->assertUnauthorized();
    }
}
