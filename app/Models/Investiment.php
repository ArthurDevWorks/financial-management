<?php

namespace App\Models;

use App\Enums\FixedIncomeIndexer;
use App\Enums\FixedIncomeProfitabilityType;
use App\Enums\InvestmentAssetType;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investiment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'dt_investment',
        'value',
        'type',
        'quantity',
        'average_price',
        'current_balance',
        'profitability',
        'profitability_type',
        'indexer',
        'contracted_rate',
        'maturity_date',
        'liquidity',
    ];

    protected $casts = [
        'dt_investment' => 'date',
        'value' => 'decimal:2',
        'type' => InvestmentAssetType::class,
        'quantity' => 'decimal:8',
        'average_price' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'profitability' => 'decimal:2',
        'profitability_type' => FixedIncomeProfitabilityType::class,
        'indexer' => FixedIncomeIndexer::class,
        'contracted_rate' => 'decimal:4',
        'maturity_date' => 'date',
    ];

    public function valuations()
    {
        return $this->hasMany(InvestimentValuation::class);
    }

    public function investedAmount(): float
    {
        $quantity = (float) ($this->quantity ?? 0);
        $averagePrice = (float) ($this->average_price ?? $this->value ?? 0);

        return round($quantity * $averagePrice, 2);
    }

    public function balance(): float
    {
        if ($this->current_balance !== null) {
            return round((float) $this->current_balance, 2);
        }

        if ($this->type?->isFixedIncome()) {
            return $this->estimatedFixedIncomeBalance();
        }

        return $this->investedAmount();
    }

    public function profitabilityPercentage(): float
    {
        return match (true) {
            $this->type?->isFixedIncome() => $this->fixedIncomeProfitabilityPercentage(),
            default => $this->markToMarketProfitabilityPercentage(),
        };
    }

    public function gainLoss(): float
    {
        return round($this->balance() - $this->investedAmount(), 2);
    }

    private function markToMarketProfitabilityPercentage(): float
    {
        $investedAmount = $this->investedAmount();

        if ($investedAmount <= 0) {
            return 0;
        }

        return round(($this->gainLoss() / $investedAmount) * 100, 2);
    }

    private function fixedIncomeProfitabilityPercentage(): float
    {
        $investedAmount = $this->investedAmount();

        if ($investedAmount <= 0) {
            return 0;
        }

        return round((($this->balance() - $investedAmount) / $investedAmount) * 100, 2);
    }

    private function estimatedFixedIncomeBalance(): float
    {
        $investedAmount = $this->investedAmount();

        if (
            $investedAmount <= 0
            || $this->contracted_rate === null
            || ! $this->isPrefixBasedFixedIncome()
        ) {
            return $investedAmount;
        }

        $investmentDate = $this->dt_investment;

        if (! $investmentDate instanceof CarbonInterface) {
            return $investedAmount;
        }

        $elapsedDays = max(0, $investmentDate->diffInDays(now()));
        $annualRate = (float) $this->contracted_rate / 100;

        return round($investedAmount * (1 + ($annualRate * ($elapsedDays / 365))), 2);
    }

    private function isPrefixBasedFixedIncome(): bool
    {
        return $this->profitability_type === FixedIncomeProfitabilityType::PREFIXED
            || $this->indexer === FixedIncomeIndexer::PREFIXED;
    }
}
