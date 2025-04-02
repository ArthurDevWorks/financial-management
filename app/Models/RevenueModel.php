<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RevenueModel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'account_id',
        'categorie_id',
        'name',
        'value',
        'dt_revenue',
        'observations'
    ];

    //Aqui abaixo virao os relacionamentos
}
