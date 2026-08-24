<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\KycDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KycController extends Controller
{
    public function show(Request $request): View
    {
        $documents = $request->user()->kycDocuments()->latest()->get();

        return view('security.kyc', ['documents' => $documents]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'document_type' => ['required', 'in:id_card,passport,drivers_license,selfie,proof_of_address'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $user = $request->user();

        $path = $request->file('file')->store('kyc/'.$user->id, 'local');

        KycDocument::create([
            'user_id' => $user->id,
            'document_type' => $request->input('document_type'),
            'file_path' => $path,
            'status' => 'pending',
        ]);

        if ($user->kyc_status === 'unverified') {
            $user->update(['kyc_status' => 'pending']);
        }

        ActivityLog::record($user->id, 'kyc_document_uploaded', ['document_type' => $request->input('document_type')]);

        return redirect()->route('security.kyc')->with('status', 'kyc-document-uploaded');
    }
}
