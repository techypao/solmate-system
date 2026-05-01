<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ContactMessagePageController extends Controller
{
    public function show()
    {
        return view('admin.contact-messages');
    }
}
