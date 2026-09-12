<?php

namespace Database\Seeders;

use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminStaffSeeder extends Seeder
{
    public function run(): void
    {
        if (Staff::where('email', $email = env('ADMIN_EMAIL', 'admin@mykpi.test'))->exists()) {
            return;
        }

        $adminPosition = StaffPosition::where('position_name', 'Administrator')->firstOrFail();
        $team = Team::first();

        $password = env('ADMIN_PASSWORD') ?: Str::password(12);

        Staff::create([
            'staff_name' => 'System Administrator',
            'email' => $email,
            'password' => Hash::make($password),
            'is_active' => true,
            'position_id' => $adminPosition->id,
            'team_id' => $team->id,
        ]);

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Seeded admin account: {$email} / {$password}");
        }
    }
}
