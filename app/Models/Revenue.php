<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Revenue extends Model
{
    use SoftDeletes;

    protected $fillable = [
        //'account_id',
        'category_id',
        'name',
        'value',
        'dt_revenue',
        'observations'
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
