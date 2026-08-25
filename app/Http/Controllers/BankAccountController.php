<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BankAccountController extends Controller
{
    public function index(Request $request): View
    {
        return view('profile.bank-accounts', [
            'bankAccounts' => $request->user()->bankAccounts()->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $user = $request->user();

        $account = $user->bankAccounts()->create($data);

        if ($data['is_default'] || $user->bankAccounts()->count() === 1) {
            $this->makeDefault($user, $account);
        }

        ActivityLog::record($user->id, 'bank_account_added', ['bank_name' => $account->bank_name]);

        return back()->with('status', 'bank-account-added');
    }

    public function update(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_unless($bankAccount->user_id === $request->user()->id, 403);

        $data = $this->validated($request);
        $bankAccount->update($data);

        if ($data['is_default']) {
            $this->makeDefault($request->user(), $bankAccount);
        }

        ActivityLog::record($request->user()->id, 'bank_account_updated', ['bank_account_id' => $bankAccount->id]);

        return back()->with('status', 'bank-account-updated');
    }

    public function setDefault(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_unless($bankAccount->user_id === $request->user()->id, 403);

        $this->makeDefault($request->user(), $bankAccount);

        return back()->with('status', 'bank-account-default-set');
    }

    public function destroy(Request $request, BankAccount $bankAccount): RedirectResponse
    {
        abort_unless($bankAccount->user_id === $request->user()->id, 403);

        $bankAccount->delete();

        ActivityLog::record($request->user()->id, 'bank_account_deleted', ['bank_account_id' => $bankAccount->id]);

        return back()->with('status', 'bank-account-deleted');
    }

    protected function makeDefault(User $user, BankAccount $account): void
    {
        $user->bankAccounts()->where('id', '!=', $account->id)->update(['is_default' => false]);
        $account->update(['is_default' => true]);
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:150'],
            'account_name' => ['required', 'string', 'max:150'],
            'account_number' => ['required', 'string', 'max:34'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['is_default'] = $request->boolean('is_default');
        $validated['status'] = 'active';

        return $validated;
    }
}
