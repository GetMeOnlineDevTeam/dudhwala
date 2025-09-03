<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityMembers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class CommunityMembersController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:community_members.view'))->only('index'),
        (new Middleware('can:community_members.create'))->only('create','store'),
        (new Middleware('can:community_members.edit'))->only('edit','update'),
        (new Middleware('can:community_members.delete'))->only('destroy'),
        (new Middleware('can:community_members.priority'))->only('updatePriority'),
    ];
}



    public function index(Request $request)
    {
        $q = CommunityMembers::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                    ->orWhere('designation', 'like', "%{$s}%");
            });
        }

        $members = $q->orderBy('priority')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->only('search'));

        return view('admin.Community.index', compact('members'));
    }

    public function create()
    {
        return view('admin.Community.create'); // create mode
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'designation' => 'nullable|string|max:120',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('community_members', 'public');
        }

        // Auto-assign next priority (append to end)
        $nextPriority      = (int) (CommunityMembers::max('priority') ?? 0) + 1;
        $data['priority']  = $nextPriority;

        // Optional: default new members to visible; comment out if you prefer DB default
        // $data['is_visible'] = true;

        CommunityMembers::create($data);

        return redirect()
            ->route('admin.community-members')
            ->with('success', 'Member created successfully.');
    }


    public function edit(CommunityMembers $member)
    {
        return view('admin.Community.edit', compact('member'));
    }

    public function update(Request $request, CommunityMembers $member)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:120',
            'designation' => 'nullable|string|max:120',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($member->image) {
                Storage::disk('public')->delete($member->image);
            }
            $data['image'] = $request->file('image')->store('community_members', 'public');
        }


        $member->update($data);

        return redirect()
            ->route('admin.community-members')
            ->with('success', 'Member updated successfully.');
    }

    public function destroy(CommunityMembers $member)
    {
        DB::transaction(function () use ($member) {

            if ($member->image) {
                Storage::disk('public')->delete($member->image);
            }
            $member->delete();
            $rows = CommunityMembers::orderBy('priority')
                ->orderBy('id')
                ->lockForUpdate()
                ->get(['id', 'priority']);

            $expected = 1;
            foreach ($rows as $row) {
                if ((int) $row->priority !== $expected) {
                    CommunityMembers::where('id', $row->id)->update(['priority' => $expected]);
                }
                $expected++;
            }
        });

        return redirect()
            ->route('admin.community-members')
            ->with('success', 'Member deleted and priority order updated.');
    }

    public function updatePriority(Request $request, CommunityMembers $member)
    {
        $data = $request->validate([
            'priority' => 'required|integer|min:1',
        ]);

        $old = (int) ($member->priority ?? 0);
        $max = (int) CommunityMembers::count();
        $new = (int) min(max(1, $data['priority']), $max); // clamp within [1..max]

        if ($old === $new) {
            return back()->with('success', 'Priority unchanged.');
        }

        DB::transaction(function () use ($member, $old, $new) {
            // Shift the affected range
            if ($new < $old) {
                // Moving up: push down everyone in [new .. old-1]
                CommunityMembers::whereBetween('priority', [$new, $old - 1])->increment('priority');
            } else {
                // Moving down: pull up everyone in [old+1 .. new]
                CommunityMembers::whereBetween('priority', [$old + 1, $new])->decrement('priority');
            }

            // Put the member at the new slot
            $member->update(['priority' => $new]);
        });

        return back()->with('success', 'Priority updated.');
    }
}
