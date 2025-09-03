<?php
namespace App\Services;

use App\Models\RolePermission;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    public function check(string $role, string $permission): bool
    {
        $role = strtolower($role);
        $permission = strtolower($permission);

        $map = Cache::remember('role_permission_map', 300, function () {
            $rows = RolePermission::with('permission:id,name')->get();
            $out = [];
            foreach ($rows as $rp) {
                $r = strtolower($rp->role);
                $p = strtolower($rp->permission?->name ?? '');
                if ($p) $out[$r][$p] = true;
            }
            return $out;
        });

        return isset($map[$role][$permission]);
    }

    public function flush(): void
    {
        Cache::forget('role_permission_map');
    }
}
