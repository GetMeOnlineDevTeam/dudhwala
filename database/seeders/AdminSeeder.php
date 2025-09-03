<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // If your User model uses fillable/guarded, this avoids mass-assignment issues in seeders
        Model::unguard();

        // Admin
        $this->upsertUser([
            'email'              => 'admin@gmail.com',
            'name'               => 'Admin User',     // used if 'name' column exists
            'first_name'         => 'Admin',          // used if 'first_name' exists
            'last_name'          => 'User',           // used if 'last_name' exists
            'contact_number'     => '9999999999',     // used if 'contact_number' exists
            'is_verified'        => 1,                // used if 'is_verified' exists
            'email_verified_at'  => now(),            // used if 'email_verified_at' exists
            // If OTP-only login: set password => null
            'password'           => 'admin@123',      // hashed automatically if 'password' exists
            'role'               => 'admin',          // <- REQUIRED: your roles live in users.role
        ]);

        // Superadmin
        $this->upsertUser([
            'email'              => 'superadmin@gmail.com',
            'name'               => 'Super Admin',
            'first_name'         => 'Super',
            'last_name'          => 'Admin',
            'phone'              => '9999999997',
            'contact_number'     => '9999999997',
            'is_verified'        => 1,
            'email_verified_at'  => now(),
            // If OTP-only login: set password => null
            'password'           => 'superadmin@123',
            'role'               => 'superadmin',
        ]);

        Model::reguard();
    }

    /**
     * Upsert a user by email, writing only columns that actually exist.
     */
    private function upsertUser(array $data): void
    {
        $table = 'users';
        $payload = [];

        if (!Schema::hasTable($table)) {
            $this->command?->warn("Table '{$table}' not found; skipping AdminSeeder.");
            return;
        }

        // Candidate columns we might populate (only if present in your schema)
        $candidates = [
            'name',
            'first_name',
            'last_name',
            'email',
            'phone',
            'contact_number',
            'is_verified',
            'email_verified_at',
            'password',
            'role',
        ];

        foreach ($candidates as $col) {
            if (!Schema::hasColumn($table, $col)) {
                continue;
            }
            if (!array_key_exists($col, $data)) {
                continue;
            }

            // Hash password if provided (set to null for OTP-only setups)
            if ($col === 'password') {
                $payload[$col] = $data[$col] ? Hash::make($data[$col]) : null;
            } else {
                $payload[$col] = $data[$col];
            }
        }

        // Safety: ensure role is written if the column exists
        if (Schema::hasColumn($table, 'role') && isset($data['role'])) {
            $payload['role'] = $data['role'];
        }

        // Required unique key: email
        User::updateOrCreate(['email' => $data['email']], $payload);
    }
}
