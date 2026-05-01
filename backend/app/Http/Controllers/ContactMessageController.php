<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    /**
     * Public: store a new contact message.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => 'required|string|max:255',
            'email'        => 'required|email|max:255',
            'phone_number' => 'nullable|string|max:50',
            'subject'      => 'required|string|max:255',
            'message'      => 'required|string|max:5000',
        ]);

        $message = ContactMessage::create($validated);

        return response()->json([
            'message' => 'Your message has been sent successfully. Our team will get back to you soon.',
            'data'    => $message,
        ], 201);
    }

    /**
     * Admin: list all contact messages, newest first.
     */
    public function index()
    {
        $messages = ContactMessage::orderByDesc('created_at')->get();

        return response()->json(['data' => $messages]);
    }

    /**
     * Admin: update status of a contact message.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:unread,read,resolved',
        ]);

        $message = ContactMessage::findOrFail($id);
        $message->update(['status' => $validated['status']]);

        return response()->json(['data' => $message]);
    }

    /**
     * Admin: delete a contact message.
     */
    public function destroy($id)
    {
        $message = ContactMessage::findOrFail($id);
        $message->delete();

        return response()->json(['message' => 'Message deleted.']);
    }
}
