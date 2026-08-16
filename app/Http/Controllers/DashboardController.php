<?php

namespace App\Http\Controllers;

use App\Models\Account;
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

        $period = in_array($request->query('period', 'month'), ['month', 'year', 'custom'], true)
            ? $request->query('period', 'month')
            : 'month';
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

        if ($month < 1 || $month > 12) {
            $month = Carbon::now()->month;
        }
        if ($year < 2000 || $year > 2100) {
            $year = Carbon::now()->year;
        }

        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $query = Release::query()
            ->where('user_id', $user_id)
            ->where('status', 'paid');

        if ($period === 'month') {
            $query->whereMonth('date', $month)->whereYear('date', $year);
        } elseif ($period === 'year') {
            $query->whereYear('date', $year);
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $releases = $query->with(['category', 'account'])->get();

        $totalRevenue = $releases->where('type', 'revenue')->sum('amount');
        $totalExpense = $releases->where('type', 'expense')->sum('amount');
        $netBalance = $totalRevenue - $totalExpense;

        $revenuesByCategory = $releases->where('type', 'revenue')->groupBy('category.name')
            ->map(function ($group, $categoryName) {
                return [
                    'category' => $categoryName ?? 'Sem Categoria',
                    'value' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values()->all();

        $expensesByCategory = $releases->where('type', 'expense')->groupBy('category.name')
            ->map(function ($group, $categoryName) {
                return [
                    'category' => $categoryName ?? 'Sem Categoria',
                    'value' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values()->all();

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

        $accounts = Account::query()
            ->with('bank')
            ->withSum(['releases as revenue_sum' => fn ($query) => $query->where('user_id', $user_id)->where('type', 'revenue')->where('status', 'paid')], 'amount')
            ->withSum(['releases as expense_sum' => fn ($query) => $query->where('user_id', $user_id)->where('type', 'expense')->where('status', 'paid')], 'amount')
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

        $monthlyData = $this->buildMonthlyData($user_id, $period, $month, $year, $startDate, $endDate);

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
                'netBalance' => $netBalance,
            ],
            'revenuesByCategory' => $revenuesByCategory,
            'expensesByCategory' => $expensesByCategory,
            'recentTransactions' => $recentTransactions,
            'monthlyData' => $monthlyData,
            'accountsEvolution' => $accountsEvolution,
        ]);
    }

    private function buildMonthlyData(
        int $user_id,
        string $period,
        int $month,
        int $year,
        ?string $startDate,
        ?string $endDate,
    ): array {
        $months = collect();

        if ($period === 'year') {
            for ($m = 1; $m <= 12; $m++) {
                $months->push(Carbon::create($year, $m, 1));
            }
        } elseif ($period === 'custom' && $startDate && $endDate) {
            $cursor = Carbon::parse($startDate)->startOfMonth();
            $last = Carbon::parse($endDate)->startOfMonth();
            while ($cursor->lte($last)) {
                $months->push($cursor->copy());
                $cursor->addMonth();
            }
        } else {
            $cursor = Carbon::create($year, $month, 1)->subMonths(5)->startOfMonth();
            for ($i = 0; $i < 6; $i++) {
                $months->push($cursor->copy());
                $cursor->addMonth();
            }
        }

        return $months->map(function (Carbon $start) use ($user_id) {
            $end = $start->copy()->endOfMonth();
            $data = Release::query()
                ->where('user_id', $user_id)
                ->where('status', 'paid')
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'revenue' THEN amount ELSE 0 END), 0) as revenue")
                ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense")
                ->first();

            $revenue = (float) ($data->revenue ?? 0);
            $expense = (float) ($data->expense ?? 0);

            return [
                'month' => $start->format('M/Y'),
                'revenue' => $revenue,
                'expense' => $expense,
                'net' => $revenue - $expense,
            ];
        })->all();
    }
}
