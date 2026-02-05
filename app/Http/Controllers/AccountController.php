<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Bank;
use Illuminate\Http\Request;
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
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'type' => 'required|integer',
            'agency' => 'required|string|max:255',
            'account' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
        ]);

        Account::create($validated);

        return redirect()->route('accounts.index')
            ->with('success', 'Conta cadastrada com sucesso');
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Account $account)
    {
        return Inertia::render('accounts/Edit', [
            'account' => $account->load('bank'),
            'banks' => Bank::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'type' => 'required|integer',
            'agency' => 'required|string|max:255',
            'account' => 'required|string|max:255',
            'total' => 'required|numeric|min:0',
        ]);

        $account->update($validated);

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
