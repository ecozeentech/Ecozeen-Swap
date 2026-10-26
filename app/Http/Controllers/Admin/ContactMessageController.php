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

    public function bulk(Request $request): RedirectResponse
    {
        $request->validate([
            'action' => ['required', 'in:mark-read,mark-replied,delete'],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:contact_messages,id'],
        ]);

        $ids = $request->input('ids');

        match ($request->input('action')) {
            'mark-read' => ContactMessage::query()->whereIn('id', $ids)->update(['status' => 'read']),
            'mark-replied' => ContactMessage::query()->whereIn('id', $ids)->update(['status' => 'replied']),
            'delete' => ContactMessage::query()->whereIn('id', $ids)->delete(),
        };

        ActivityLog::record(auth()->id(), 'admin_bulk_'.str_replace('-', '_', $request->input('action')).'_contact_messages', ['ids' => $ids, 'count' => count($ids)]);

        $statusKey = match ($request->input('action')) {
            'mark-read' => 'messages-marked-read',
            'mark-replied' => 'messages-marked-replied',
            'delete' => 'messages-deleted',
        };

        return redirect()->route('admin.contact-messages.index')->with('status', $statusKey);
    }
}
