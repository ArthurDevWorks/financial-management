<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investiment extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name',
        'dt_investment',
        'value',
        'type',
        'profitability',
    ];

    protected $casts = [
        'dt_investment' => 'date',
        'value' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'type');
    }
}
