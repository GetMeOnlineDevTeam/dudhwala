<?php
// database/seeders/PermissionSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Permission;
use App\Models\RolePermission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('role_permissions')->truncate();
        DB::table('permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::transaction(function () {
            // === Canonical permissions (EXACTLY as you listed) ===
            $perms = [
                // Dashboard
                ['name' => 'dashboard.view', 'label' => 'View Admin Dashboard'],

                // Users
                ['name' => 'users.view', 'label' => 'View Users'],

                // Bookings
                ['name' => 'bookings.view',   'label' => 'View Bookings'],
                ['name' => 'bookings.export', 'label' => 'Export Bookings'],

                // Booking Items
                ['name' => 'booking_items.view',        'label' => 'View Booking Items'],
                ['name' => 'booking_items.bulk_upsert', 'label' => 'Bulk Upsert Booking Items'],

                // Venues
                ['name' => 'venues.view',   'label' => 'View Venues'],
                ['name' => 'venues.create', 'label' => 'Create Venue'],
                ['name' => 'venues.edit',   'label' => 'Edit Venue'],
                ['name' => 'venues.delete', 'label' => 'Delete Venue'],

                // Schedule
                ['name' => 'schedule.view',   'label' => 'View Schedule'],
                ['name' => 'schedule.create', 'label' => 'Create Schedule'],
                ['name' => 'schedule.edit',   'label' => 'Edit Schedule'],
                ['name' => 'schedule.delete', 'label' => 'Delete Schedule'],

                // Community Members
                ['name' => 'community_members.view',     'label' => 'View Community Members'],
                ['name' => 'community_members.create',   'label' => 'Create Community Member'],
                ['name' => 'community_members.edit',     'label' => 'Edit Community Member'],
                ['name' => 'community_members.delete',   'label' => 'Delete Community Member'],
                ['name' => 'community_members.priority', 'label' => 'Update Member Priority'],

                // Community Moments
                ['name' => 'community_moments.view',   'label' => 'View Community Moments'],
                ['name' => 'community_moments.create', 'label' => 'Create Community Moment'],
                ['name' => 'community_moments.edit',   'label' => 'Edit Community Moment'],
                ['name' => 'community_moments.delete', 'label' => 'Delete Community Moment'],

                // Payment Settlement
                ['name' => 'settlement.view',          'label' => 'View Settlements'],
                ['name' => 'settlement.create',        'label' => 'Create Settlement'],
                ['name' => 'settlement.update_status', 'label' => 'Update Settlement Status'],

                // Contact Requests
                ['name' => 'contact_requests.view',   'label' => 'View Contact Requests'],
                ['name' => 'contact_requests.show',   'label' => 'Show Contact Request'],
                ['name' => 'contact_requests.delete', 'label' => 'Delete Contact Request'],

                // Policy
                ['name' => 'policy.view',   'label' => 'View Policy'],
                ['name' => 'policy.edit',   'label' => 'Edit Policy'],
                ['name' => 'policy.update', 'label' => 'Update Policy'],

                // Homepage Banner
                ['name' => 'banner.edit',   'label' => 'Edit Homepage Banner'],
                ['name' => 'banner.update', 'label' => 'Update Homepage Banner'],

                // Configurations
                ['name' => 'configurations.view',   'label' => 'View Configurations'],
                ['name' => 'configurations.update', 'label' => 'Update Configurations'],

                // Invoices / Payments
                ['name' => 'invoices.create',   'label' => 'Create Invoice'],
                ['name' => 'invoices.download', 'label' => 'Download Invoice'],

                // Offline Bookings
                ['name' => 'offline_bookings.create', 'label' => 'Create Offline Booking'],
            ];

            // Upsert permissions
            foreach ($perms as $p) {
                Permission::updateOrCreate(
                    ['name' => $p['name']],
                    ['label' => $p['label'] ?? null]
                );
            }

            $allNames = array_column($perms, 'name');

            // Helper: sync a string role to exactly the provided permission set
            $sync = function (string $role, array $permissionNames): void {
                $ids = Permission::whereIn('name', $permissionNames)->pluck('id')->all();

                // add missing
                foreach ($ids as $pid) {
                    RolePermission::firstOrCreate([
                        'role'          => $role,        // <- string role column
                        'permission_id' => $pid,
                    ]);
                }

                // remove extras not in desired set
                RolePermission::where('role', $role)
                    ->whereNotIn('permission_id', $ids)
                    ->delete();
            };

            // Superadmin => everything (even if you also use Gate::before, keeping this explicit doesn't hurt)
            $sync('superadmin', $allNames);

            // Admin => start with the exact list (trim later if client restricts)
            $sync('admin', $allNames);
        });
    }
}
