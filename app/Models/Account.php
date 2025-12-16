<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'agency',
        'account',
        'total',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revenues()
    {
        return $this->hasMany(Revenue::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
