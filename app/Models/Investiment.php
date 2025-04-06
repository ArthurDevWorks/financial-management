<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Investiment extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'category_id',
        'name',
        'dt_investiment',
        'value',
        'profitability'
    ];

    //Relantionship
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
