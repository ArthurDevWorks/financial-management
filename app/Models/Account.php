<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bank_id',
        'type',
        'agency',
        'account',
        'total',
    ];

    protected $appends = ['current_balance'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function getRevenueSumAttribute($value)
    {
        return number_format((float) ($value ?? 0), 2, '.', '');
    }

    public function getExpenseSumAttribute($value)
    {
        return number_format((float) ($value ?? 0), 2, '.', '');
    }

    public function getCurrentBalanceAttribute()
    {
        $revenues = (float) ($this->revenue_sum ?? 0);
        $expenses = (float) ($this->expense_sum ?? 0);

        return (float) $this->total + $revenues - $expenses;
    }
}
