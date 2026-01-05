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
        'profitability',
    ];

    protected $casts = [
        'dt_investment' => 'date',
    ];

    public function categories()
    {
        return $this->belongsTo(Category::class);
    }
}
