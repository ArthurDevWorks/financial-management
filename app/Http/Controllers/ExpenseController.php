<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExpenseStoreRequest;
use App\Http\Requests\ExpenseUpdateRequest;
use App\Models\Expense;
use App\Models\Account;
use App\Models\Category;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index()
    {
        return Inertia::render('expenses/Index', [
            'expenses' => Expense::query()
                ->with(['account', 'category'])
                ->orderBy('dt_expense', 'desc')
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('expenses/Create', [
            'accounts' => Account::all(),
            'categories' => Category::where('type', 'expense')->get(),
        ]);
    }

    public function store(ExpenseStoreRequest $request)
    {
        Expense::create($request->validated());

        return redirect()->route('expenses.index')
            ->with('success', 'Despesa cadastrada com sucesso');
    }

    public function edit(Expense $expense)
    {
        return Inertia::render('expenses/Edit', [
            'expense' => $expense->load(['account', 'category']),
            'accounts' => Account::all(),
            'categories' => Category::where('type', 'expense')->get(),
        ]);
    }

    public function update(ExpenseUpdateRequest $request, Expense $expense)
    {
        $expense->update($request->validated());

        return redirect()->route('expenses.index')
            ->with('success', 'Despesa atualizada com sucesso');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Despesa removida com sucesso');
    }
}
