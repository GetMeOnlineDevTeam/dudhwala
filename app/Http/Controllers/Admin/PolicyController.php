<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policies;
use Illuminate\Http\Request;

class PolicyController extends Controller
{

    public function index(Request $request)
    {

        $type = $request->input('type');

        $query = Policies::query();

        if ($type) {
            $query->where('type', $type);
        }

        $policies = $query->get();

        return view('admin.Policy.index', compact('policies'));
    }


    public function edit($id)
    {
        $policy = Policies::findOrFail($id);

        return view('admin.Policy.edit', compact('policy'));
    }


    public function show($id)
    {
        $policy = Policies::findOrFail($id);

        return view('admin.Policy.show', compact('policy'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'text' => 'required|string',
        ]);

        $policy = Policies::findOrFail($id);

        $policy->update([
            'title' => $request->input('title'),
            'text' => $request->input('text'),
        ]);

        return redirect()->route('admin.policy.index')->with('success', 'Policy updated successfully.');
    }
}
