<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestimentValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'investiment_id',
        'assumptions',
        'projected_cash_flows',
        'summary',
        'calculated_at',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'projected_cash_flows' => 'array',
        'summary' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function investiment()
    {
        return $this->belongsTo(Investiment::class);
    }
}
