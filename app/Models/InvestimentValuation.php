<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestimentValuation extends Model
{
    use HasFactory;

    public const METHOD_DCF = 'dcf';

    public const METHOD_PRECO_TETO = 'preco_teto';

    protected $fillable = [
        'investiment_id',
        'method',
        'assumptions',
        'projected_cash_flows',
        'summary',
        'calculated_at',
    ];

    protected $casts = [
        'assumptions' => 'array',
        'projected_cash_flows' => 'array',
        'summary' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function investiment()
    {
        return $this->belongsTo(Investiment::class);
    }

    public function methodLabel(): string
    {
        return match ($this->method) {
            self::METHOD_PRECO_TETO => 'Preço Teto Projetivo',
            default => 'Fluxo de Caixa Descontado',
        };
    }
}
