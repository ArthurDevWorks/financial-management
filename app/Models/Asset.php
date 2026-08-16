<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticker', 'name', 'asset_type',
        'sector', 'subsector', 'segment',
        'current_price', 'market_cap', 'enterprise_value', 'volume_avg_30d',
        'dividend_yield', 'price_to_earnings', 'price_to_book', 'ev_to_ebitda',
        'price_to_sales', 'price_to_assets', 'price_to_cash_flow',
        'roe', 'roa', 'profit_margin', 'ebitda_margin', 'gross_margin',
        'ebitda', 'net_debt', 'gross_debt',
        'debt_to_ebitda', 'net_debt_to_ebitda', 'current_liquidity', 'payout',
        'net_income', 'revenue', 'free_cash_flow',
        'dividends_per_share', 'earnings_per_share', 'book_value_per_share',
        'total_shares',
        'logo_url', 'long_business_summary', 'website', 'full_time_employees',
        'p_vp', 'cap_rate', 'vacancy_rate', 'vacancy_financial',
        'average_maturity', 'number_of_properties', 'rental_area', 'ffo_yield',
        'net_worth', 'manager',
        'fetched_at',
    ];

    protected function casts(): array
    {
        return [
            'current_price' => 'decimal:2',
            'market_cap' => 'decimal:2',
            'enterprise_value' => 'decimal:2',
            'volume_avg_30d' => 'decimal:2',
            'dividend_yield' => 'decimal:4',
            'price_to_earnings' => 'decimal:2',
            'price_to_book' => 'decimal:2',
            'ev_to_ebitda' => 'decimal:2',
            'roe' => 'decimal:4',
            'roa' => 'decimal:4',
            'profit_margin' => 'decimal:4',
            'ebitda_margin' => 'decimal:4',
            'gross_margin' => 'decimal:4',
            'ebitda' => 'decimal:2',
            'net_debt' => 'decimal:2',
            'gross_debt' => 'decimal:2',
            'debt_to_ebitda' => 'decimal:2',
            'net_debt_to_ebitda' => 'decimal:2',
            'current_liquidity' => 'decimal:2',
            'payout' => 'decimal:4',
            'net_income' => 'decimal:2',
            'revenue' => 'decimal:2',
            'free_cash_flow' => 'decimal:2',
            'dividends_per_share' => 'decimal:4',
            'earnings_per_share' => 'decimal:4',
            'book_value_per_share' => 'decimal:4',
            'p_vp' => 'decimal:2',
            'cap_rate' => 'decimal:4',
            'vacancy_rate' => 'decimal:4',
            'vacancy_financial' => 'decimal:4',
            'average_maturity' => 'decimal:2',
            'number_of_properties' => 'integer',
            'rental_area' => 'decimal:2',
            'ffo_yield' => 'decimal:4',
            'net_worth' => 'decimal:2',
            'fetched_at' => 'datetime',
        ];
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(InvestimentValuation::class, 'asset_id');
    }

    public function isFii(): bool
    {
        return $this->asset_type === 'fii';
    }

    public function isStock(): bool
    {
        return $this->asset_type === 'stock';
    }
}
