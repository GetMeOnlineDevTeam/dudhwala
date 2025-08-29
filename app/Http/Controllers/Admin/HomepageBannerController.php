<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Facades\Storage;

class HomepageBannerController extends Controller
{
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
