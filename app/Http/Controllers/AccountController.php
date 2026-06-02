<?php

namespace App\Http\Controllers;

use App\Enums\AccountType;
use App\Http\Requests\AccountStoreRequest;
use App\Http\Requests\AccountUpdateRequest;
use App\Models\Account;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Response;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Inertia::render('accounts/Index', [
            'accounts' => Account::query()
                ->where('user_id', Auth::id())
                ->with(['bank'])
                ->withSum(['releases as revenue_sum' => fn($query) => $query->where('type', 'revenue')], 'amount')
                ->withSum(['releases as expense_sum' => fn($query) => $query->where('type', 'expense')], 'amount')
                ->when($request->search, fn($q, $search) => $q->where(function($q) use ($search) {
                    $q->where('account', 'like', "%{$search}%")
                      ->orWhereHas('bank', fn($q) => $q->where('name', 'like', "%{$search}%"));
                }))
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

    public function export()
    {
        $accounts = Account::query()
            ->where('user_id', Auth::id())
            ->with(['bank'])
            ->withSum(['releases as revenue_sum' => fn($q) => $q->where('type', 'revenue')], 'amount')
            ->withSum(['releases as expense_sum' => fn($q) => $q->where('type', 'expense')], 'amount')
            ->orderBy('created_at', 'desc')
            ->get();

        $callback = function () use ($accounts) {
            $handle = fopen('php://output', 'w');
            fputs($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, ['ID', 'Banco', 'Conta', 'Tipo', 'Saldo Inicial', 'Saldo Atual']);

            foreach ($accounts as $account) {
                fputcsv($handle, [
                    $account->id,
                    $account->bank?->name ?? '-',
                    $account->account,
                    $account->type,
                    number_format($account->total, 2, ',', '.'),
                    number_format($account->current_balance, 2, ',', '.'),
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="contas.csv"',
        ]);
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
