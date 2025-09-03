<?php

namespace App\Http\Controllers\Admin;

use App\Models\Configuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ConfigurationController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:configurations.view'))->only('index'),
        (new Middleware('can:configurations.update'))->only('update'),
    ];
}


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
