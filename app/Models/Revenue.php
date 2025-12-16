<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id',
        'category_id',
        'name',
        'value',
        'dt_revenue',
        'description',
    ];

    protected $casts = [
        'dt_revenue' => 'date',
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
