<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCard extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_id',
        'name',
        'limit',
        'closing_day',
        'due_day',
        'color',
    ];

    protected $casts = [
        'limit'       => 'float',
        'closing_day' => 'integer',
        'due_day'     => 'integer',
    ];

    // ── Relações ──────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    // ── Cálculo de fatura ─────────────────────────────────────────────────────

    /**
     * Dado um mês e ano de referência, retorna o intervalo (start, end) da fatura.
     *
     * Lógica: a fatura "de Julho" vai do dia seguinte ao fechamento de Junho
     * até o dia de fechamento de Julho.
     *
     * Ex: closing_day = 10
     *   Fatura Jul/2026 → 11/Jun/2026 a 10/Jul/2026
     */
    public function invoicePeriod(int $month, int $year): array
    {
        $closingDay = $this->closing_day;

        $closingDate = Carbon::create($year, $month, $closingDay);

        $startDate = (clone $closingDate)->subMonth()->addDay(); // dia seguinte ao fechamento anterior

        return [
            'start' => $startDate->toDateString(),
            'end'   => $closingDate->toDateString(),
            'due'   => Carbon::create($year, $month, $this->due_day)->toDateString(),
        ];
    }

    /**
     * Retorna os releases que compõem a fatura de um dado mês/ano.
     */
    public function invoiceReleases(int $month, int $year)
    {
        $period = $this->invoicePeriod($month, $year);

        return $this->releases()
            ->with(['category'])
            ->whereBetween('date', [$period['start'], $period['end']])
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Soma dos lançamentos da fatura de um dado mês/ano.
     */
    public function invoiceTotal(int $month, int $year): float
    {
        $period = $this->invoicePeriod($month, $year);

        return (float) $this->releases()
            ->where('type', 'expense')
            ->whereBetween('date', [$period['start'], $period['end']])
            ->sum('amount');
    }

    /**
     * Total da fatura atual (período aberto no momento).
     */
    public function currentInvoiceTotal(): float
    {
        $today = Carbon::today();

        // Se hoje é antes ou no dia de fechamento, a fatura "aberta" é do mês atual
        if ($today->day <= $this->closing_day) {
            return $this->invoiceTotal($today->month, $today->year);
        }

        // Caso contrário já fechou esse mês, a fatura aberta é do próximo mês
        $next = $today->copy()->addMonth();
        return $this->invoiceTotal($next->month, $next->year);
    }

    /**
     * Mês/ano da fatura aberta atual.
     */
    public function currentInvoiceMonthYear(): array
    {
        $today = Carbon::today();

        if ($today->day <= $this->closing_day) {
            return ['month' => $today->month, 'year' => $today->year];
        }

        $next = $today->copy()->addMonth();
        return ['month' => $next->month, 'year' => $next->year];
    }
}
