<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class HomepageBannerController extends Controller implements HasMiddleware
{
public static function middleware(): array
{
    return [
        new Middleware('auth:admin'),
        new Middleware('role:admin,superadmin'),
        (new Middleware('can:banner.edit'))->only('edit'),
        (new Middleware('can:banner.update'))->only('update'),
    ];
}

 public function edit()
    {
        $banner = Banner::first();  // Get the first and only banner
        return view('admin.banner.index', compact('banner'));
    }

    public function update(Request $request)
    {

        $request->validate([
            'banner' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5024',  
        ]);

        $banner = Banner::first();

        if ($request->hasFile('banner')) {
            
            if ($banner->banner) {
                Storage::delete('public/' . $banner->banner);  
            }

            $fileName = 'banner_' . time() . '.' . $request->file('banner')->getClientOriginalExtension(); 
            $imagePath = $request->file('banner')->storeAs('banner', $fileName, 'public');  // Store on public disk
            $banner->banner = 'banner/' . $fileName;    
        }

        $banner->is_published = true;
        $banner->save();

        return redirect()->route('admin.banner.edit')->with('success', 'Banner updated successfully!');
    }
}
