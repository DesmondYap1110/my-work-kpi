# MyKPI user guide

Read by the KPI Assistant (app/Ai/Tools/AppGuide.php). Each "## key" heading is
one topic the assistant can look up. Keep headings lowercase-with-dashes, and
update the matching section whenever a screen changes.

## overview

MyKPI measures each member's performance with a KPI score out of 100.

- The score has two parts: **project points** (from project tasks the member completes) and **KPI objective points** (from the marks given in their appraisals).
- How the 100 points split between the two is set per position under KPI Setting (for example 50 projects / 50 objectives, or 30 / 70).
- Administrators set up positions, KPI objectives, project tags, projects and appraisals, and see the KPI Report.
- Members see My KPI, My Tasks and My Appraisal, and fill in their own self-assessment.

Set-up order for a new company:
1. Human Resource > Team: add teams.
2. Human Resource > Position: add positions.
3. For each position, open its KPI (click "No" in the KPI Assigned column) and set it up - see kpi-setting.
4. Settings > Project Tag Setting: add tags and their points - see project-tags.
5. Human Resource > Member: add members with their team and position.
6. Project Setup > Project: create projects and add tasks with an assignee and a tag.
7. Appraisal > Review: open appraisals; Appraisal > Schedule: set how often each member is reviewed.
8. Settings > Project Form Setup: set the performance bands (Outstanding, Good, Poor...).

## kpi-setting

KPI Setting is set **per position**. Open it from Human Resource > Position, then click the KPI Assigned cell ("No" / "Yes") for that position. The page has three parts:

**1. Project KPI (how the 100 points split)**
- Drag the bar to decide how many of the 100 points come from Projects and how many from KPI objectives. The two always add up to 100.
- "Marks needed for full project points" (the target): the task marks a member must earn in the period to get all the project points. Example: target 30 marks, member earns 24 → 80% of the project points.
- With no target, project points are measured against the marks of the tasks the member was given.
- Click the ? next to it for a worked example: a member with 80% on both parts scores 80 / 100.

**2. Project Tags**
- The kinds of work this position is tagged with (for example "task", "new feature", "bug").
- Add an existing tag or create a new one with its points. A completed task earns its tag's points towards the project marks.
- Removing a tag here only stops it being offered for this position; tasks already tagged keep their points.

**3. KPI Objectives**
- **Add Category** (for example Technical, Leadership). A category holds objectives.
- Inside a category, **Add Objective** (for example Technical Knowledge).
- Inside an objective, add **items** - the actual things that get marked (for example "Relevant functional knowledge").
- Each item has **Allowed Marks** - the marks an appraiser may pick, for example 5, 4, 3, 2, 1. An item marked 1-3 is out of 3, not 5.
- A position needs at least one item with allowed marks before members can be added to it.

Tips:
- Keep the split realistic for the role: project-heavy roles (developers) often use 50-70 on projects; roles with little project work can use 0.
- Items with no allowed marks cannot be scored.

## project-tags

Settings > Project Tag Setting (administrator only).
- A tag is a kind of work with **points**, for example "epic" 13, "new feature" 4, "task" 0.6.
- **Positions**: choose which positions may use the tag (a searchable multi-select). Leave it empty for all positions.
- Add a row at the bottom of the table, edit with the pencil, delete with the bin.
- When a task with a tag is marked Done, the assignee earns the tag's points in their project marks.
- Deleting a tag keeps its points on tasks that already use it.
- Tags can also be added for one position from that position's KPI Setting page.

## projects-and-tasks

Project Setup > Project and Project Setup > Task (members can open projects too).
- Create a project with its title and dates, then open it and **Add Task**.
- Each task has an assignee (one member), a tag (which decides its points), a due date, a priority and a status: To Do, In Progress, Review, Blocked, Done.
- Only **Done** tasks earn points. Moving a task to Done records the completion date.
- A task counts for a KPI period if it is due in that period or completed in it.
- A project that has tasks cannot be cancelled; finish or remove the tasks first.
- Members see their own work under My Tasks.

## kpi-score

How the KPI score out of 100 is worked out (the same on My KPI, a member's KPI page and the KPI Report):

- **Project points** = project achievement % × the position's project share.
  Project achievement % = task marks earned ÷ the position's target (capped at 100%), or ÷ marks of assigned tasks when there is no target.
- **KPI objective points** = objective achievement % × the objective share.
  Objective achievement % = marks given ÷ best possible marks, on the member's **generated** appraisals that cover the period. The reviewer's mark counts; the employee's own mark only stands in where the reviewer left an item blank. Unmarked items are left out.
- **KPI score** = project points + objective points.
- If one part has nothing to score (no project work, or no generated appraisal yet), the other part counts for the full 100.
- Worked example: split 50/50, member earns 24 of a 30-mark target (80% → 40 points) and 80% on objectives (40 points) → KPI score 80 / 100.
- The performance band (Outstanding, Good, Poor...) comes from Settings > Project Form Setup.

## appraisal

Appraisal > Review (administrator).
1. Click **New Appraisal**. Pick the member, the review period (From / To), review date, **Review Every** (how often they are appraised) and Next Assessment (filled in from the cycle).
2. The review form opens. It shows the member's project tasks in the period and the KPI objectives from their position.
3. Mark each item in the **Reviewer** column. The **Employee** column is the member's own self-assessment - only the member can fill it.
4. **Save Draft** keeps it private while you work. **Save & Generate** saves and shares it with the member; its marks then count in their KPI score.
5. **Reopen to Edit** takes a generated appraisal back to draft (the member stops seeing it).
- A member can have only one draft appraisal open at a time.
- **Export CSV** downloads the form; **Print** prints it.
- The Appraisal list can be filtered by member, team, position, status and period, and exported to CSV.

## self-assessment

For members, under My Appraisal.
- While an appraisal is a **draft**, open it and choose **Your mark** for each item, then **Save My Marks**. You can change them until the appraiser generates it.
- You cannot see the reviewer's marks or the score until it is generated.
- After it is generated you can read the whole review, but not change your marks.

## review-schedule

Appraisal > Schedule (administrator).
- Set how often each member is appraised: Manual, 1 week, 1/2/3/4/6 months or 1 year. It saves as soon as you pick.
- Next due = the last generated appraisal's Next Assessment date, otherwise its period end plus the cycle, otherwise the join date plus the cycle.
- Filter by member, team or status (Overdue, Due soon, Scheduled, Manual, Draft open). The Start button opens New Appraisal for that member.
- "Notify me N days before due": members due within N days show as due soon here and on the dashboard. Overdue members always show.

## performance-bands

Settings > Project Form Setup (administrator).
- A band names a score range and its outcome, for example 90-100 Outstanding (Pass), 60-69 Satisfactory (Extend), 0-49 Poor (Fail).
- Add, edit and delete bands in the table. Ranges may not overlap.
- The band a KPI score falls in shows on appraisals, the KPI Report and team status.

## kpi-report

KPI Report in the sidebar (administrator).
- Filters: period (this month, last month, this quarter, this year, last year, custom), team, position, member.
- KPI Summary: highest, lowest and average KPI score, and total projects.
- Performance Trend: monthly average KPI, project and objective scores.
- Project Report: tasks completed, points earned and completion rate per project.
- Task Breakdown: points earned by tag.
- Team Performance: each team's average KPI, top performer, members and status, with a chart.
- Member Ranking: every member ranked by KPI score.
- **Export CSV** (choose which table) and **Print** use the filters on screen.

## members-teams-positions

Human Resource (administrator).
- Team: add or rename teams, activate or deactivate them.
- Position: add positions and their job scope; open a position's KPI from the KPI Assigned column.
- Member: add a member with name, email, IC, contact, team, position and join date; edit, deactivate or open their KPI page.
- A member can only be added to a position that already has a KPI with at least one markable item.

## theme-setting

Settings > Theme Setting (administrator).
- Pick a colour theme (Ocean Blue, Indigo, Forest, Light Green, Slate), then change any main colour: primary, secondary, sidebar, mobile menu, accent, page background, text. The page previews as you choose; Save Theme applies it for everyone.
- Login Page: choose a background image (built-in, upload your own, or colour only), a background colour and how much to darken the image.
- Reset to Default goes back to the original theme.

## password

Settings > Change Password: enter the current password and the new one twice.
Forgot Password on the login page emails a reset link (email sending must be configured by the administrator).
