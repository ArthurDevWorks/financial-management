<?php

namespace App\Console\Commands;

use App\Services\AssetSyncService;
use Illuminate\Console\Command;

class SyncAssets extends Command
{
    protected $signature = 'assets:sync
        {--ticker= : Sincronizar apenas um ticker específico}
        {--type=all : Tipo de ativo (stock, fii, all)}
        {--force : Ignorar cache e forçar atualização}
        {--hours=4 : Máximo de horas desde a última atualização}';

    protected $description = 'Sincroniza dados de ativos (StatusInvest + Brapi)';

    public function handle(AssetSyncService $syncService): int
    {
        $ticker     = $this->option('ticker');
        $type       = $this->option('type');
        $force      = $this->option('force');
        $hours      = (int) $this->option('hours');

        $this->info('Iniciando sincronização de ativos...');
        $this->newLine();

        $start = now();

        if ($ticker)
        {
            $success = $syncService->syncSingle($ticker, $force);

            if ($success)
            {
                $this->info("Ativo {$ticker} sincronizado com sucesso.");
            } else {
                $this->warn("Não foi possível sincronizar {$ticker}.");
            }

            return $success ? Command::SUCCESS : Command::FAILURE;
        }

        $result = $syncService->sync(
            type: $type,
            force: $force,
            maxHoursSinceUpdate: $hours,
            onProgress: function (string $ticker, ?int $current = null, ?int $total = null) {
                if ($current !== null && $total !== null) {
                    $this->output->write(sprintf(
                        "\r  [%d/%d] %s",
                        $current, $total, $ticker,
                    ));
                }
            },
        );

        $this->newLine(2);

        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Ativos processados', $result['processed']],
                ['Atualizados', $result['updated']],
                ['Erros', $result['errors']],
                ['Duração', $start->diffInSeconds(now()) . 's'],
            ],
        );

        if ($result['errors'] > 0) {
            $this->newLine();
            $this->warn('Alguns ativos tiveram erro. Verifique os logs para mais detalhes.');
        }

        return Command::SUCCESS;
    }
}
