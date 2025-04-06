<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'name',
        'balance'
    ];

    //Relantionship
    public function revenues()
    {
        return $this->hasMany(Revenue::class, 'revenue_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'expense_id');
    }
}
