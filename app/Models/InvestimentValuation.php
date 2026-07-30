<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestimentValuation extends Model
{
    use HasFactory;

    public const METHOD_DCF = 'dcf';

    public const METHOD_PRECO_TETO = 'preco_teto';

    public const METHOD_GORDON = 'gordon';

    protected $fillable = [
        'asset_id',
        'method',
        'assumptions',
        'calculated_at',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            self::METHOD_PRECO_TETO => 'Preço Teto Projetivo',
            self::METHOD_GORDON => 'Gordon Growth Model',
            default => 'Fluxo de Caixa Descontado',
        };
    }
}
