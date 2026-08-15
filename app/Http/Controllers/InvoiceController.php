<?php

namespace App\Http\Controllers;

use App\Models\CreditCard;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Exibe os lançamentos de uma fatura específica (mês/ano) de um cartão.
     */
    public function show(CreditCard $creditCard, int $year, int $month)
    {
        if ($creditCard->user_id !== Auth::id()) {
            abort(403);
        }

        if ($month < 1 || $month > 12 || $year < 2000 || $year > 2100) {
            abort(404);
        }

        $period = $creditCard->invoicePeriod($month, $year);
        $releases = $creditCard->invoiceReleases($month, $year);

        $total = (float) max(0,
            $releases->where('type', 'expense')->sum('amount')
            - $releases->where('type', 'revenue')->sum('amount')
        );

        // Lista de meses disponíveis: 12 meses anteriores + atual + 2 futuros
        $months = collect();
        $ref = Carbon::today()->startOfMonth()->subMonths(11);
        for ($i = 0; $i < 15; $i++) {
            $m = $ref->copy()->addMonths($i);
            $months->push([
                'month' => $m->month,
                'year' => $m->year,
                'label' => ucfirst($m->translatedFormat('M/Y')),
            ]);
        }

        $currentMY = $creditCard->currentInvoiceMonthYear();

        return Inertia::render('credit-cards/Invoices', [
            'card' => [
                'id' => $creditCard->id,
                'name' => $creditCard->name,
                'color' => $creditCard->color,
                'limit' => $creditCard->limit,
                'closing_day' => $creditCard->closing_day,
                'due_day' => $creditCard->due_day,
                'bank' => $creditCard->bank ? ['name' => $creditCard->bank->name] : null,
            ],
            'period' => $period,
            'releases' => $releases,
            'total' => $total,
            'month' => $month,
            'year' => $year,
            'months' => $months,
            'current_month' => $currentMY['month'],
            'current_year' => $currentMY['year'],
        ]);
    }
}
