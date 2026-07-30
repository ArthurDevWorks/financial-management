<?php

namespace App\Console\Commands;

use App\Models\RecurrencePlan;
use Illuminate\Console\Command;

class GenerateRecurringReleases extends Command
{
    protected $signature = 'releases:generate-recurring';

    protected $description = 'Generate releases for active recurring plans whose next generation date is due';

    public function handle(): void
    {
        $plans = RecurrencePlan::where('active', true)
            ->where('next_generation', '<=', today())
            ->get();

        if ($plans->isEmpty()) {
            $this->info('Nenhum plano recorrente pendente de geração.');
            return;
        }

        $generated = 0;

        foreach ($plans as $plan) {
            $release = $plan->generateNextRelease();

            if ($release) {
                $this->info("Gerado lançamento #{$release->id} para o plano #{$plan->id} - {$plan->title}");
                $generated++;
            }
        }

        $this->info("{$generated} lançamento(s) recorrente(s) gerado(s) com sucesso.");
    }
}
