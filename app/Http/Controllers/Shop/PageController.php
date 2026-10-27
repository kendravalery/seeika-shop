<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactUsMail;

class PageController extends Controller
{
    public function about()
    {
        return view('storefront.about');
    }

    public function faq()
    {
        return view('storefront.faq');
    }

    public function contactUsShow()
    {
        return view('storefront.contact');
    }
    public function contactUs(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);
        // This will be sent to the email
        Mail::to('kendramahardhika20@gmail.com')->send(new ContactUsMail($request->all()));

        return back()->with('success', 'Your message was sent successfully');
    }
}
