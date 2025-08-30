<?php

namespace App\Http\Controllers\Admin;

use App\Models\Configuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ConfigurationController extends Controller
{
    // Show the configuration form
    public function index()
    {
        $configurations = Configuration::all();
        return view('admin.configurations.index', compact('configurations'));
    }

    // Update the configuration
    public function update(Request $request)
    {
        // Remove _token and _method from request
        $data = $request->except('_token', '_method');

        if (isset($data['configurations'])) {
            foreach ($data['configurations'] as $config) {
                if (!empty($config['key'])) {
                    Configuration::updateOrCreate(
                        ['key' => $config['key']],
                        ['value' => $config['value']]
                    );
                }
            }
        }

        return redirect()->back()->with('success', 'Configurations updated successfully.');
    }
}
