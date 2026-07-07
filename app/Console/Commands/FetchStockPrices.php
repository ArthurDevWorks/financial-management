<?php

namespace App\Console\Commands;

use App\Models\Investiment;
use App\Services\BrapiService;
use Illuminate\Console\Command;

class FetchStockPrices extends Command
{
    protected $signature = 'investiments:fetch-prices {--chunk=50 : Quantos ativos buscar por vez}';

    protected $description = 'Atualiza cotações de todos os ativos via brapi.dev';

    public function handle(BrapiService $brapi): void
    {
        $chunkSize = (int) $this->option('chunk');

        $this->info('Buscando cotações...');

        $total = Investiment::query()
            ->whereNotNull('name')
            ->get()
            ->reject(fn (Investiment $i) => $i->type?->isFixedIncome())
            ->count();

        if ($total === 0) {
            $this->warn('Nenhum ativo elegível para atualização.');

            return;
        }

        $this->info("{$total} ativo(s) encontrado(s).");
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        Investiment::query()
            ->whereNotNull('name')
            ->chunk($chunkSize, function ($investiments) use ($brapi, $bar): void {
                $symbols = $investiments
                    ->reject(fn (Investiment $i) => $i->type?->isFixedIncome())
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->all();

                if (empty($symbols)) {
                    $bar->advance($investiments->count());

                    return;
                }

                $quotes = $brapi->fetchQuotes($symbols);
                $now = now();

                foreach ($investiments as $investiment) {
                    $quote = $quotes->get(strtoupper($investiment->name));

                    if ($quote === null) {
                        $bar->advance();

                        continue;
                    }

                    $investiment->update([
                        'current_balance' => $quote['price'],
                        'logo_url' => $quote['logourl'],
                        'last_price_fetched_at' => $now,
                    ]);

                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info('Cotações atualizadas com sucesso!');
    }
}
