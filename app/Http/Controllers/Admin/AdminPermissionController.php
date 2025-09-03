<?php
// app/Http/Controllers/Admin/AdminPermissionController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminPermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:admin'),
            new Middleware('role:superadmin'),
        ];
    }

    public function index()
    {
        $roleName = 'admin';

        $permissions = Permission::orderBy('name')->get();

        $assignedNames = Permission::query()
            ->whereIn('id', RolePermission::where('role', $roleName)->pluck('permission_id'))
            ->pluck('name')
            ->toArray();

        $grouped = $permissions->groupBy(function ($perm) {
            $n = strtolower($perm->name ?? '');
            if (str_starts_with($n, 'manage_')) return substr($n, 7);
            if (str_starts_with($n, 'view_'))   return substr($n, 5);
            if (str_contains($n, '.'))          return explode('.', $n, 2)[0];
            if (str_contains($n, '_'))          return explode('_', $n, 2)[1] ?? $n;
            return $n;
        });

        return view('admin.permissions.admin', [
            'roleName' => $roleName,
            'grouped'  => $grouped,
            'assigned' => $assignedNames,
        ]);
    }

    public function update(Request $request)
    {
        $roleName = 'admin';

        $selected = (array) $request->input('permissions', []);
        $valid = Permission::whereIn('name', $selected)->pluck('id', 'name');

        DB::transaction(function () use ($roleName, $valid) {
            RolePermission::where('role', $roleName)
                ->whereNotIn('permission_id', $valid->values())
                ->delete();

            $existingIds = RolePermission::where('role', $roleName)->pluck('permission_id')->all();
            $toInsert = $valid->values()->diff($existingIds)->values();

            foreach ($toInsert as $pid) {
                RolePermission::create(['role' => $roleName, 'permission_id' => $pid]);
            }
        });

        if (app()->bound(\App\Services\PermissionService::class)) {
            app(\App\Services\PermissionService::class)->flush();
        }

        return back()->with('success', 'Admin permissions updated successfully.');
    }
}
