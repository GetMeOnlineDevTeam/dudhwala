<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContactRequest;

class ContactRequestController extends Controller
{
    /**
     * Handle submission of the contact form.
     */
    public function sendContact(Request $request)
    {
        // 1) Validate
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'phone_no'   => 'required|string|max:50',
            'subject'    => 'required|string|max:255',
            'message'    => 'required|string',
        ]);

        // 2) Persist
        ContactRequest::create($data);

        // 3) Redirect back with a success message
        return back()->with('success', 'Thank you! Your message has been received.');
    }
}
