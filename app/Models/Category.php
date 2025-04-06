<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'type',
        'name'
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

    public function investments()
    {
        return $this->hasMany(Investiment::class, 'investiment_id');
    }
}
