<?php

namespace App\Models;

use App\Enums\InvestmentAssetType;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Investiment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'dt_investment',
        'value',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'dt_investment' => 'datetime',
    ];

    public function getTypeAttribute($value)
    {
        if (is_numeric($value)) {
            $category = DB::table('categories')->where('id', (int) $value)->first();

            if ($category) {
                return InvestmentAssetType::fromLegacyCategoryName($category->name);
            }

            return null;
        }

        try {
            return InvestmentAssetType::from($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function getCurrentBalanceAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }

        return $this->attributes['value'] ?? null;
    }
}
