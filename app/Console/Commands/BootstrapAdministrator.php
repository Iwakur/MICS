<?php

namespace App\Console\Commands;

use App\Enums\StaffCompensationMode;
use App\Enums\UserRole;
use App\Models\Staff;
use App\Models\StaffRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:bootstrap-administrator')]
#[Description('Interactively create the first linked production administrator and staff identity')]
class BootstrapAdministrator extends Command
{
    public function handle(): int
    {
        if (User::query()->where('role', UserRole::Admin)->where('is_active', true)->exists()) {
            $this->error('An active administrator already exists. Use the administrator UI for additional accounts.');

            return self::FAILURE;
        }

        $data = [
            'username' => $this->ask('Administrator username'),
            'email' => $this->ask('Administrator email'),
            'first_name' => $this->ask('Staff first name'),
            'family_name' => $this->ask('Staff family name (optional)'),
            'password' => $this->secret('Password (minimum 12 characters)'),
        ];
        $validator = Validator::make($data, [
            'username' => ['required', 'alpha_dash', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'first_name' => ['required', 'string', 'max:100'],
            'family_name' => ['nullable', 'string', 'max:100'],
            'password' => ['required', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        DB::transaction(function () use ($data): void {
            $role = StaffRole::query()->firstOrCreate(
                ['name' => 'Administrator'],
                ['can_teach' => false, 'is_active' => true, 'note' => 'Bootstrap administrator staff role.'],
            );
            $staff = Staff::query()->create([
                'staff_role_id' => $role->id,
                'first_name' => $data['first_name'],
                'family_name' => $data['family_name'],
                'email' => $data['email'],
                'compensation_mode' => StaffCompensationMode::Dynamic,
                'salary_amount' => null,
                'is_active' => true,
                'note' => 'Created by the one-time administrator bootstrap command.',
            ]);
            User::query()->create([
                'staff_id' => $staff->id,
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => UserRole::Admin,
                'is_active' => true,
            ]);
        });

        $this->info('Linked administrator and staff profile created. The command will now refuse to run again.');

        return self::SUCCESS;
    }
}
