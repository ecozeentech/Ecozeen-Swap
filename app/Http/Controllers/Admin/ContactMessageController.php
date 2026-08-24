<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactMessageController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->when($request->input('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contact-messages.index', ['messages' => $messages]);
    }

    public function show(ContactMessage $contactMessage): View
    {
        if ($contactMessage->status === 'new') {
            $contactMessage->update(['status' => 'read']);
        }

        return view('admin.contact-messages.show', ['message' => $contactMessage]);
    }

    public function markReplied(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->update(['status' => 'replied']);

        return back()->with('status', 'message-marked-replied');
    }

    public function destroy(ContactMessage $contactMessage): RedirectResponse
    {
        $contactMessage->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_contact_message', ['id' => $contactMessage->id]);

        return redirect()->route('admin.contact-messages.index')->with('status', 'message-deleted');
    }
}
