<?php

namespace App\Services;

class SectorMapper
{
    /**
     * Mapeamento BrAPI (Yahoo Finance) sector → setor normalizado PT.
     */
    private const SECTOR_MAP = [
        'Energy'                 => 'Energia',
        'Financial Services'     => 'Financeiro',
        'Technology'             => 'Tecnologia',
        'Healthcare'             => 'Saúde',
        'Industrials'            => 'Indústria',
        'Consumer Cyclical'      => 'Consumo Cíclico',
        'Consumer Defensive'     => 'Consumo Não Cíclico',
        'Basic Materials'        => 'Materiais Básicos',
        'Communication Services' => 'Comunicação',
        'Real Estate'            => 'Imobiliário',
        'Utilities'              => 'Utilidades Públicas',
    ];

    /**
     * Mapeamento BrAPI industry → sub-setor normalizado.
     */
    private const INDUSTRY_MAP = [
        // Energia
        'Oil & Gas Integrated'            => 'Petróleo e Gás',
        'Oil & Gas Midstream'             => 'Petróleo e Gás',
        'Oil & Gas Exploration & Production' => 'Petróleo e Gás',
        'Oil & Gas Refining & Marketing'  => 'Refino e Distribuição',
        'Oil & Gas Equipment & Services'  => 'Petróleo e Gás',
        'Oil & Gas Pipelines'             => 'Petróleo e Gás',
        'Solar'                           => 'Energias Renováveis',
        'Utilities—Regulated Electric'    => 'Energia Elétrica',
        'Utilities—Regulated Gas'         => 'Gás',
        'Utilities—Diversified'           => 'Utilidades',
        'Utilities—Renewable'             => 'Energias Renováveis',
        'Electric Utilities'              => 'Energia Elétrica',
        'Gas Utilities'                   => 'Gás',
        'Water Utilities'                 => 'Água',
        'Foreign Utilities'               => 'Utilidades',

        // Financeiro
        'Banks—Regional'                       => 'Bancos',
        'Banks—Diversified'                    => 'Bancos',
        'Banks'                                => 'Bancos',
        'Insurance—Life'                       => 'Seguradoras',
        'Insurance—Property & Casualty'        => 'Seguradoras',
        'Insurance—Diversified'                => 'Seguradoras',
        'Insurance—Brokers'                    => 'Seguradoras',
        'Asset Management'                     => 'Gestão de Ativos',
        'Capital Markets'                      => 'Mercado de Capitais',
        'Credit Services'                      => 'Crédito',
        'Financial Data & Stock Exchanges'     => 'Financeiro',
        'Financial Conglomerates'              => 'Financeiro',
        'Insurance—Specialty Insurance'        => 'Seguradoras',
        'Mortgage Finance'                     => 'Crédito',

        // Tecnologia
        'Software—Application'          => 'Software',
        'Software—Infrastructure'       => 'Software',
        'Information Technology Services' => 'Serviços de TI',
        'Semiconductors'                => 'Semicondutores',
        'Semiconductor Equipment & Materials' => 'Semicondutores',
        'Computer Hardware'             => 'Hardware',
        'Electronics & Computer Distribution' => 'Hardware',
        'Scientific & Technical Instruments' => 'Tecnologia',
        'Internet Content & Information' => 'Tecnologia',
        'Electronic Gaming & Multimedia' => 'Jogos',
        'Internet Retail'               => 'E-commerce',

        // Consumo Cíclico
        'Auto Manufacturers'            => 'Automotivo',
        'Auto Parts'                    => 'Automotivo',
        'Restaurants'                   => 'Alimentação',
        'Grocery Stores'                => 'Varejo Alimentício',
        'Discount Stores'               => 'Varejo',
        'Home Improvement Retail'       => 'Varejo',
        'Specialty Retail'              => 'Varejo',
        'Department Stores'             => 'Varejo',
        'Apparel Manufacturing'         => 'Têxtil',
        'Footwear & Accessories'        => 'Têxtil',
        'Textile Manufacturing'         => 'Têxtil',
        'Luxury Goods'                  => 'Têxtil',
        'Internet Content & Information' => 'Tecnologia',
        'Leisure'                       => 'Lazer',
        'Lodging'                       => 'Hotelaria',
        'Travel Services'               => 'Turismo',
        'Casinos & Gaming'              => 'Jogos',
        'Entertainment'                 => 'Entretenimento',
        'Publishing'                    => 'Mídia',
        'Advertising Agencies'          => 'Mídia',
        'Broadcasting'                  => 'Mídia',
        'Movies & Entertainment'        => 'Entretenimento',

        // Consumo Não Cíclico
        'Packaged Foods'                => 'Alimentos',
        'Food Distribution'             => 'Alimentos',
        'Confectioners'                 => 'Alimentos',
        'Beverages—Non-Alcoholic'       => 'Bebidas',
        'Beverages—Breweries'           => 'Bebidas',
        'Beverages—Wineries & Distilleries' => 'Bebidas',
        'Tobacco'                       => 'Tabaco',
        'Household & Personal Products' => 'Produtos de Limpeza',
        'Farm Products'                 => 'Agronegócio',

        // Materiais Básicos
        'Gold'                          => 'Mineração',
        'Silver'                        => 'Mineração',
        'Copper'                        => 'Mineração',
        'Steel'                         => 'Siderurgia',
        'Aluminum'                      => 'Mineração',
        'Other Industrial Metals'       => 'Mineração',
        'Thermal Coal'                  => 'Carvão',
        'Coking Coal'                   => 'Carvão',
        'Building Materials'            => 'Materiais de Construção',
        'Chemicals'                     => 'Químicos',
        'Specialty Chemicals'           => 'Químicos',
        'Agricultural Inputs'           => 'Agronegócio',
        'Paper & Paper Products'        => 'Papel e Celulose',
        'Forestry Production'           => 'Papel e Celulose',

        // Saúde
        'Drug Manufacturers—General'           => 'Farmacêutica',
        'Drug Manufacturers—Specialty & Generic' => 'Farmacêutica',
        'Medical Devices'                      => 'Dispositivos Médicos',
        'Medical Instruments & Supplies'       => 'Dispositivos Médicos',
        'Diagnostics & Research'               => 'Diagnóstico',
        'Health Information Services'          => 'Saúde',
        'Medical Care Facilities'              => 'Saúde',
        'Pharmaceutical Retailers'             => 'Farmácia',

        // Indústria
        'Engineering & Construction'           => 'Construção',
        'Farm & Heavy Construction Machinery'  => 'Construção',
        'Aerospace & Defense'                  => 'Defesa',
        'Specialty Industrial Machinery'       => 'Indústria',
        'Industrial Distribution'              => 'Distribuição Industrial',
        'Building Products'                    => 'Construção',
        'Conglomerates'                        => 'Indústria',
        'Waste Management'                     => 'Saneamento',
        'Environmental Services'               => 'Saneamento',
        'Staffing & Employment Services'       => 'Serviços',
        'Consulting Services'                  => 'Serviços',
        'Security & Protection Services'       => 'Serviços',
        'Rental & Leasing Services'            => 'Serviços',
        'Trucking'                             => 'Transporte',
        'Airlines'                             => 'Transporte',
        'Marine Shipping'                      => 'Transporte',
        'Railroads'                            => 'Transporte',
        'Integrated Freight & Logistics'       => 'Logística',
        'Airports & Air Services'              => 'Transporte',
        'Infrastructure Operations'            => 'Infraestrutura',

        // Consumo
        'Medical Distribution'                 => 'Distribuição',

        // Imobiliário
        'REIT—Office'                         => 'Lajes',
        'REIT—Residential'                    => 'Residencial',
        'REIT—Retail'                         => 'Shoppings',
        'REIT—Industrial'                     => 'Logística',
        'REIT—Healthcare Facilities'          => 'Saúde',
        'REIT—Hotel & Motel'                  => 'Hotelaria',
        'REIT—Diversified'                    => 'Diversificado',
        'Real Estate—Diversified'             => 'Diversificado',
        'Real Estate—Development'             => 'Incorporação',
        'Real Estate—Operating'               => 'Operação',
        'Real Estate Services'                => 'Serviços Imobiliários',

        // Comunicação
        'Telecom Services'                    => 'Telecomunicações',
        'Communication Equipment'             => 'Equipamentos',
        'Electronic Components'               => 'Componentes Eletrônicos',
        'Electronic Equipment & Parts'        => 'Equipamentos Eletrônicos',
        'Electrical Equipment & Parts'        => 'Elétrica',
    ];

    /**
     * Mapeamento StatusInvest sectorname PT → normalizado.
     * Apenas para valores que precisam de ajuste.
     */
    private const STATUS_INVEST_MAP = [
        'Comércio'       => 'Varejo',
        'Consumo'        => 'Consumo Cíclico',
        'Comunicações'   => 'Comunicação',
        'Transporte'     => 'Transporte',
        'Saneamento'     => 'Saneamento',
        'Agropecuária'   => 'Agronegócio',
    ];

    /**
     * Normaliza setor e sub-setor a partir dos valores brutos da API.
     *
     * @param  string|null  $sector   Setor vindo da API (BrAPI EN ou StatusInvest PT)
     * @param  string|null  $industry Indústria/sub-setor vindo da API
     * @return array{sector: string|null, subsector: string|null}
     */
    public static function normalize(?string $sector, ?string $industry = null): array
    {
        $normalizedSector = self::SECTOR_MAP[$sector] ?? $sector;
        $normalizedSubsector = $industry !== null
            ? (self::INDUSTRY_MAP[$industry] ?? $industry)
            : null;

        // Se veio do StatusInvest (já em PT), aplica normalização adicional
        if ($normalizedSector !== null && isset(self::STATUS_INVEST_MAP[$normalizedSector])) {
            $normalizedSector = self::STATUS_INVEST_MAP[$normalizedSector];
        }

        return [
            'sector' => self::clean($normalizedSector),
            'subsector' => self::clean($normalizedSubsector),
        ];
    }

    /**
     * Retorna todos os setores normalizados conhecidos (para referência/testes).
     */
    public static function knownSectors(): array
    {
        return array_unique(array_values(self::SECTOR_MAP));
    }

    /**
     * Retorna todos os sub-setores normalizados conhecidos (para referência/testes).
     */
    public static function knownSubsectors(): array
    {
        return array_unique(array_values(self::INDUSTRY_MAP));
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
