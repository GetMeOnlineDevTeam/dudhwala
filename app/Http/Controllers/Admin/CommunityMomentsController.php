<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityMoment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
class CommunityMomentsController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:community_moments.view'))->only('index'),
        (new Middleware('can:community_moments.create'))->only('create','store'),
        (new Middleware('can:community_moments.edit'))->only('edit','update'),
        (new Middleware('can:community_moments.delete'))->only('destroy'),
    ];
}


    public function index(Request $request)
    {
        $q = CommunityMoment::query();

        if ($request->filled('search')) {
            $s = $request->search;
            $q->where('description', 'like', "%{$s}%");
        }

        $moments = $q->orderByDesc('id')
            ->paginate(10)
            ->appends($request->only('search'));

        return view('admin.Moments.index', compact('moments'));
    }

    public function create()
    {
        return view('admin.Moments.create'); // create mode
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'image'       => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('community_moments', 'public');
        }

        CommunityMoment::create($data);

        return redirect()->route('admin.community-moments')->with('success', 'Community Moment created successfully.');
    }

    public function edit(CommunityMoment $moment)
    {
        return view('admin.Moments.edit', compact('moment'));
    }

    public function update(Request $request, CommunityMoment $moment)
    {
        $data = $request->validate([
            'description' => 'required|string|max:255',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($moment->image) {
                Storage::disk('public')->delete($moment->image);
            }
            $data['image'] = $request->file('image')->store('community_moments', 'public');
        }

        $moment->update($data);

        return redirect()->route('admin.community-moments')->with('success', 'Community Moment updated successfully.');
    }

    public function destroy(CommunityMoment $moment)
    {
        // Delete the image file from storage
        if ($moment->image) {
            Storage::disk('public')->delete($moment->image);
        }

        $moment->delete();

        return redirect()->route('admin.community-moments')->with('success', 'Community Moment deleted successfully.');
    }
}
