<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'account_id',
        'category_id',
        'name',
        'value',
        'dt_expense',
        'description',
    ];

    protected $casts = [
        'dt_expense' => 'date',
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
