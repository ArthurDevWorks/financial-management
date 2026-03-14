<?php

namespace App\Models;

use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Release;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
    ];

    protected $casts = [
        'type' => CategoryType::class,
    ];

    public function releases()
    {
        return $this->hasMany(Release::class);
    }

    public function investments()
    {
        return $this->hasMany(Investiment::class);
    }
}
