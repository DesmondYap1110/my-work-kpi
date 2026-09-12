<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Renames every legacy column to lowercase snake_case with readable words.
 *
 * The schema was carried over from the legacy PHP app and mixed several
 * conventions - p_PTitle, kojbInfo_id, position_ID, createddate, staffimg.
 * Each name below says what it holds, in one consistent style.
 *
 * MySQL 8's RENAME COLUMN updates dependent foreign keys automatically, so
 * the constraints don't need dropping and recreating.
 */
return new class extends Migration
{
    /**
     * table => [old column => new column]
     */
    private function map(): array
    {
        return [
            'staff_position' => [
                'position_ID' => 'position_id',
                'kpistatus' => 'has_kpi',
            ],
            'team' => [
                'team_status' => 'is_active',
            ],
            'staff' => [
                'staffimg' => 'photo',
                'staff_address' => 'address',
                'datejointeam' => 'team_joined_date',
                'datejoincompany' => 'company_joined_date',
                'staffstatus' => 'is_active',
            ],
            'kpi_objective_info' => [
                'kojbInfo_id' => 'objective_info_id',
                'kojbInfo_title' => 'title',
            ],
            'kpi_objective' => [
                'obj_id' => 'objective_id',
                'position_ID' => 'position_id',
                'kojbInfo_id' => 'objective_info_id',
                'obj_type' => 'objective_type',
            ],
            'kpi_objective_mark' => [
                'kobjmark_id' => 'mark_id',
                'obj_id' => 'objective_id',
            ],
            'project' => [
                'p_Title' => 'title',
                'p_addDate' => 'added_date',
                'p_SDate' => 'start_date',
                'p_EDate' => 'end_date',
                'date_assign' => 'assigned_date',
                'p_status' => 'status',
            ],
            'project_phase' => [
                'p_PID' => 'phase_id',
                'p_ID' => 'project_id',
                'p_PTitle' => 'title',
                'p_Type' => 'type',
                'p_SDate' => 'start_date',
                'p_DDate' => 'due_date',
                'p_Remark' => 'remark_file',
                'p_Invoice' => 'invoice_file',
                'p_Status' => 'approval_status',
                'p_ppstatus' => 'progress_status',
                'p_SubmitDate' => 'submitted_date',
            ],
            'project_phase_files' => [
                'p_pID' => 'phase_id',
                'PPfilename' => 'filename',
                'PPdatetime' => 'uploaded_at',
                'staff_ID' => 'staff_id',
            ],
            'project_kpi' => [
                'kpiproject_id' => 'project_kpi_id',
                'position_ID' => 'position_id',
                'kojbInfo_id' => 'objective_info_id',
                'createddate' => 'submitted_at',
            ],
            'user_logs' => [
                'user_IP' => 'ip_address',
            ],
        ];
    }

    public function up(): void
    {
        $this->rename($this->map());
    }

    public function down(): void
    {
        $reversed = [];

        foreach ($this->map() as $table => $columns) {
            $reversed[$table] = array_flip($columns);
        }

        $this->rename($reversed);
    }

    private function rename(array $map): void
    {
        foreach ($map as $table => $columns) {
            foreach ($columns as $from => $to) {
                DB::statement("ALTER TABLE `{$table}` RENAME COLUMN `{$from}` TO `{$to}`");
            }
        }
    }
};
