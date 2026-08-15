<?php

namespace App\Services;

class FiiSegmentMapper
{
    /**
     * Mapeamento BrAPI segmentType → segmento normalizado.
     */
    private const SEGMENT_MAP = [
        'tijolo'   => 'Tijolo',
        'Tijolo'   => 'Tijolo',
        'TIJOLO'   => 'Tijolo',
        'papel'    => 'Papel',
        'Papel'    => 'Papel',
        'PAPEL'    => 'Papel',
        'híbrido'  => 'Híbrido',
        'Híbrido'  => 'Híbrido',
        'HIBRIDO'  => 'Híbrido',
        'fof'      => 'Fundo de Fundos',
        'Fundo de Fundos' => 'Fundo de Fundos',
        'FOF'      => 'Fundo de Fundos',
        'fii'      => 'FII',
        'FII'      => 'FII',
        'hedge'    => 'Hedge',
        'Hedge'    => 'Hedge',
        'HEDGE'    => 'Hedge',
    ];

    /**
     * Mapeamento BrAPI segmentoAtuacao → sub-segmento normalizado.
     * Valores de operação do FII (para FIIs tijolo).
     */
    private const SUBSEGMENT_MAP = [
        'Logística'           => 'Logística',
        'Logistica'           => 'Logística',
        'Shoppings'           => 'Shoppings',
        'Shopping'            => 'Shoppings',
        'Lajes Corporativas'  => 'Lajes Corporativas',
        'Lajes'               => 'Lajes Corporativas',
        'Residencial'         => 'Residencial',
        'Multicategoria'      => 'Multicategoria',
        'Varejo'              => 'Varejo',
        'Educacional'         => 'Educacional',
        'Saúde'               => 'Saúde',
        'Hotel'               => 'Hotelaria',
        'Hotelaria'           => 'Hotelaria',
        'Industrial'          => 'Industrial',
        'Corporativo'         => 'Corporativo',
        'Mix'                 => 'Multicategoria',
        'Híbrido'             => 'Híbrido',
    ];

    /**
     * Mapeamento StatusInvest segmentname → segmento normalizado.
     * StatusInvest usa nomes diferentes do BrAPI.
     */
    private const STATUS_INVEST_SEGMENT_MAP = [
        'Tijolo'              => 'Tijolo',
        'Papel'               => 'Papel',
        'Fundo de Fundos'     => 'Fundo de Fundos',
        'Híbrido'             => 'Híbrido',
        'Hedge'               => 'Hedge',
        'Multimercado'        => 'Hedge',
        'Logística'           => 'Tijolo',
        'Shoppings'           => 'Tijolo',
        'Lajes Corporativas'  => 'Tijolo',
        'Residencial'         => 'Tijolo',
        'Varejo'              => 'Tijolo',
        'Educacional'         => 'Tijolo',
        'Saúde'               => 'Tijolo',
        'Hotel'               => 'Tijolo',
        'Hospitalar'          => 'Tijolo',
        'Corporativo'         => 'Tijolo',
        'Industrial'          => 'Tijolo',
        'Galpão'              => 'Tijolo',
        'Imobiliário'         => 'Tijolo',
    ];

    /**
     * Normaliza o segmento de um FII.
     *
     * @param  string|null $segmentType    Segmento principal (BrAPI segmentType ou StatusInvest segmentname)
     * @param  string|null $subsegment     Sub-segmento (BrAPI segmentoAtuacao)
     * @param  string|null $assetType      Tipo do ativo (fii, stock, etc.)
     * @return array{sector: string|null, subsector: string|null, segment: string|null}
     */
    public static function normalize(?string $segmentType, ?string $subsegment = null, ?string $assetType = null): array
    {
        // Só aplica para FIIs
        if ($assetType !== null && $assetType !== 'fii') {
            return [
                'sector' => null,
                'subsector' => null,
                'segment' => null,
            ];
        }

        // Normaliza o segmento principal
        $normalizedSegment = self::SEGMENT_MAP[$segmentType]
            ?? self::STATUS_INVEST_SEGMENT_MAP[$segmentType]
            ?? $segmentType;

        // Normaliza o sub-segmento
        $normalizedSubsegment = self::SUBSEGMENT_MAP[$subsegment] ?? $subsegment;

        return [
            'sector' => 'FII',
            'subsector' => self::clean($normalizedSubsegment),
            'segment' => self::clean($normalizedSegment),
        ];
    }

    /**
     * Retorna todos os segmentos conhecidos.
     */
    public static function knownSegments(): array
    {
        return array_unique(array_values(self::SEGMENT_MAP));
    }

    /**
     * Retorna todos os sub-segmentos conhecidos.
     */
    public static function knownSubsegments(): array
    {
        return array_unique(array_values(self::SUBSEGMENT_MAP));
    }

    private static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }
}
