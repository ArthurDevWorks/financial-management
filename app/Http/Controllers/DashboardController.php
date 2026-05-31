<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Investiment;
use App\Models\Release;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user_id = Auth::id();

        // Defaults to current month
        $period = $request->query('period', 'month');
        $month = $request->query('month', Carbon::now()->month);
        $year = $request->query('year', Carbon::now()->year);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Release::query()
            ->where('user_id', $user_id);

        if ($period === 'month') {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        } elseif ($period === 'year') {
            $query->whereYear('date', $year);
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $releases = $query->with(['category', 'account'])->get();

        // Totals
        $totalRevenue = $releases->where('type', 'revenue')->sum('amount');
        $totalExpense = $releases->where('type', 'expense')->sum('amount');
        $netBalance = $totalRevenue - $totalExpense;

        // Group by category for revenues
        $revenuesByCategory = $releases->where('type', 'revenue')->groupBy('category.name')
            ->map(function ($group, $categoryName) {
                return [
                    'category' => $categoryName ?? 'Sem Categoria',
                    'value' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values()->all();

        // Group by category for expenses
        $expensesByCategory = $releases->where('type', 'expense')->groupBy('category.name')
            ->map(function ($group, $categoryName) {
                return [
                    'category' => $categoryName ?? 'Sem Categoria',
                    'value' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values()->all();

        // Transações Recentes
        $recentTransactions = $releases->sortByDesc('date')->take(10)->map(function ($r) {
            return [
                'id' => $r->id,
                'type' => $r->type,
                'description' => $r->title,
                'category' => $r->category ? $r->category->name : 'Sem Categoria',
                'value' => (float) $r->amount,
                'date' => $r->date->format('Y-m-d'),
                'account' => $r->account ? $r->account->account : 'Sem Conta',
            ];
        })->values()->all();

        // Contas e saldos
        // Total Base
        $accounts = Account::query()
            ->with('bank')
            ->withSum(['releases as revenue_sum' => fn ($query) => $query->where('type', 'revenue')], 'amount')
            ->withSum(['releases as expense_sum' => fn ($query) => $query->where('type', 'expense')], 'amount')
            ->where('user_id', $user_id)
            ->get();

        $totalInitialBalance = $accounts->sum('total');

        $accountsEvolution = $accounts->map(function ($acc) {
            $accRevenues = (float) ($acc->revenue_sum ?? 0);
            $accExpenses = (float) ($acc->expense_sum ?? 0);
            $saldoInicial = (float) $acc->total;

            return [
                'account' => $acc->account,
                'bank' => $acc->bank ? $acc->bank->name : 'Outro',
                'balance' => $saldoInicial + $accRevenues - $accExpenses,
                'initialBalance' => $saldoInicial,
            ];
        })->values()->all();

        $totalBalance = collect($accountsEvolution)->sum('balance');

        $investiments = Investiment::query()->get();
        $totalInvestment = round($investiments->sum(fn (Investiment $investiment): float => $investiment->balance()), 2);
        $totalInvested = round($investiments->sum(fn (Investiment $investiment): float => $investiment->investedAmount()), 2);
        $totalInvestmentGainLoss = $totalInvestment - $totalInvested;
        $totalProfitability = $totalInvested > 0
            ? round(($totalInvestmentGainLoss / $totalInvested) * 100, 2)
            : 0;

        return Inertia::render('Dashboard', [
            'period' => $period,
            'month' => (int) $month,
            'year' => (int) $year,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'summary' => [
                'totalBalance' => $totalBalance,
                'totalInitialBalance' => $totalInitialBalance,
                'totalRevenue' => $totalRevenue,
                'totalExpense' => $totalExpense,
                'totalInvestment' => $totalInvestment,
                'netBalance' => $netBalance,
                'totalProfitability' => $totalProfitability,
            ],
            'revenuesByCategory' => $revenuesByCategory,
            'expensesByCategory' => $expensesByCategory,
            'recentTransactions' => $recentTransactions,
            'monthlyData' => [], // Simplifying monthly data for generic dashboard
            'accountsEvolution' => $accountsEvolution,
        ]);
    }
}
