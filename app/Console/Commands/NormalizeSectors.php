<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Services\FiiSegmentMapper;
use App\Services\NameNormalizer;
use App\Services\SectorMapper;
use Illuminate\Console\Command;

class NormalizeSectors extends Command
{
    protected $signature = 'assets:normalize-sectors {--dry-run : Mostra mudanças sem salvar}';

    protected $description = 'Normaliza setores, nomes e segmentos de todos os ativos existentes';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $assets = Asset::all();
        $total = $assets->count();
        $updated = 0;
        $skipped = 0;

        $this->newLine();
        $this->info("Total de ativos: {$total}");
        if ($dryRun) {
            $this->warn('Modo DRY-RUN: nenhuma alteração será salva.');
        }
        $this->newLine();

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($assets as $asset) {
            $original = $asset->toArray();
            $changed = false;

            // Normaliza setor
            if ($asset->sector !== null || $asset->subsector !== null) {
                if ($asset->asset_type === 'fii') {
                    $fii = FiiSegmentMapper::normalize(
                        $asset->segment ?? $asset->sector,
                        $asset->subsector,
                        'fii'
                    );
                    if ($fii['segment'] !== null && $fii['segment'] !== $asset->segment) {
                        $asset->segment = $fii['segment'];
                        $changed = true;
                    }
                    if ($fii['subsector'] !== null && $fii['subsector'] !== $asset->subsector) {
                        $asset->subsector = $fii['subsector'];
                        $changed = true;
                    }
                } else {
                    $mapped = SectorMapper::normalize($asset->sector, $asset->subsector);
                    if ($mapped['sector'] !== null && $mapped['sector'] !== $asset->sector) {
                        $asset->sector = $mapped['sector'];
                        $changed = true;
                    }
                    if ($mapped['subsector'] !== null && $mapped['subsector'] !== $asset->subsector) {
                        $asset->subsector = $mapped['subsector'];
                        $changed = true;
                    }
                }
            }

            // Normaliza nome
            $normalizedName = NameNormalizer::normalize($asset->ticker, $asset->name);
            if ($normalizedName !== null && $normalizedName !== $asset->name) {
                $asset->name = $normalizedName;
                $changed = true;
            }

            if ($changed) {
                $updated++;
                if ($dryRun) {
                    $this->newLine();
                    $this->warn("  [DRY-RUN] {$asset->ticker}:");
                    foreach (['sector', 'subsector', 'segment', 'name'] as $field) {
                        $old = $original[$field] ?? null;
                        $new = $asset->{$field} ?? null;
                        if ($old !== $new) {
                            $this->line("    {$field}: \"{$old}\" → \"{$new}\"");
                        }
                    }
                } else {
                    $asset->save();
                }
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("Simulação concluída: {$updated} para atualizar, {$skipped} sem alterações.");
        } else {
            $this->info("Normalização concluída: {$updated} ativos atualizados, {$skipped} sem alterações.");
        }

        return self::SUCCESS;
    }
}
