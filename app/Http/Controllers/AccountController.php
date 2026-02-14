<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Http\Requests\AccountStoreRequest;
use App\Http\Requests\AccountUpdateRequest;
use App\Models\Account;
use App\Models\Bank;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('accounts/Index', [
            'accounts' => Account::query()
                ->where('user_id', Auth::id())
                ->with(['bank'])
                ->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('accounts/Create', [
            'banks' => Bank::all(),
            'accountTypes' => AccountType::options(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AccountStoreRequest $request)
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Conta cadastrada com sucesso');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        return Inertia::render('accounts/Edit', [
            'account' => $account->load('bank'),
            'banks' => Bank::all(),
            'accountTypes' => AccountType::options(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AccountUpdateRequest $request, Account $account)
    {
        $account->update($request->validated());

        return redirect()->route('accounts.index')
            ->with('success', 'Conta atualizada com sucesso');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Account $account)
    {
        $account->delete();

        return redirect()->route('accounts.index')
            ->with('success', 'Conta removida com sucesso');
    }
}
