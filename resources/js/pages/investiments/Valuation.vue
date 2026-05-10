<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Calculator, Landmark, Sparkles } from 'lucide-vue-next'
import { computed, watch } from 'vue'

interface InvestmentCategory {
  id: number
  name: string
}

interface Investment {
  id: number
  name: string
  value: number
  profitability: number
  dt_investment: string
  type: number
  category?: InvestmentCategory
}

interface ValuationProjection {
  year: number
  growth_rate: number
  projected_fcf: number
  discount_factor: number
  present_value: number
}

interface ValuationSummary {
  present_value_of_cash_flows: number
  terminal_value: number
  terminal_present_value: number
  equity_value: number
  fair_value_per_share: number
  market_cap: number | null
  upside: number | null
  margin_of_safety: number | null
}

interface ValuationResult {
  assumptions: Record<string, string | number | null | string[]>
  projected_cash_flows: ValuationProjection[]
  summary: ValuationSummary
}

interface DefaultAssumptions {
  current_fcf: string
  discount_rate: string
  terminal_growth_rate: string
  projection_years: string
  total_shares: string
  payout: string
  roe: string
  current_price_per_share: string
  growth_rates: string[]
}

const props = defineProps<{
  investiment: Investment
  valuation: ValuationResult | null
  defaultAssumptions: DefaultAssumptions
}>()

const parseNumber = (value: string | number | null | undefined) => {
  const parsed = Number(value)
  return Number.isFinite(parsed) ? parsed : 0
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const formatPercent = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'N/A'
  return `${value.toFixed(2)}%`
}

const initialProjectionYears = parseNumber(props.defaultAssumptions.projection_years || 5) || 5
const initialGrowthRate = parseNumber(props.defaultAssumptions.roe || 24) * (1 - parseNumber(props.defaultAssumptions.payout || 75) / 100)

const form = useForm({
  current_fcf: props.defaultAssumptions.current_fcf ?? '',
  discount_rate: props.defaultAssumptions.discount_rate ?? '12',
  terminal_growth_rate: props.defaultAssumptions.terminal_growth_rate ?? '3',
  projection_years: props.defaultAssumptions.projection_years ?? '5',
  total_shares: props.defaultAssumptions.total_shares ?? '',
  payout: props.defaultAssumptions.payout ?? '75',
  roe: props.defaultAssumptions.roe ?? '24',
  current_price_per_share: props.defaultAssumptions.current_price_per_share ?? props.investiment.value.toString(),
  growth_rates: props.defaultAssumptions.growth_rates?.length
    ? [...props.defaultAssumptions.growth_rates]
    : Array.from({ length: initialProjectionYears }, () => initialGrowthRate.toFixed(2)),
})

const projectionYearsCount = computed(() => {
  const years = Math.trunc(parseNumber(form.projection_years) || 5)
  return Math.min(15, Math.max(3, years))
})

const projectionYearIndexes = computed(() =>
  Array.from({ length: projectionYearsCount.value }, (_, index) => index),
)

const currentPrice = computed(() => parseNumber(form.current_price_per_share || props.investiment.value))
const totalShares = computed(() => parseNumber(form.total_shares))
const marketCap = computed(() => currentPrice.value * totalShares.value)
const baseGrowthRate = computed(() => {
  const roe = parseNumber(form.roe)
  const payout = parseNumber(form.payout)
  return roe * (1 - payout / 100)
})

watch(
  projectionYearsCount,
  (years) => {
    const baseGrowthValue = baseGrowthRate.value.toFixed(2)

    if (form.growth_rates.length > years) {
      form.growth_rates = form.growth_rates.slice(0, years)
    }

    while (form.growth_rates.length < years) {
      form.growth_rates.push(baseGrowthValue)
    }

    form.growth_rates = form.growth_rates.map((growthRate) => growthRate || baseGrowthValue)
  },
  { immediate: true },
)

const summaryCards = computed(() => {
  if (!props.valuation) return []

  return [
    {
      label: 'Market cap atual',
      value: props.valuation.summary.market_cap !== null
        ? formatCurrency(props.valuation.summary.market_cap)
        : 'N/A',
      tone: 'sky',
    },
    {
      label: 'Valor presente dos fluxos',
      value: formatCurrency(props.valuation.summary.present_value_of_cash_flows),
      tone: 'emerald',
    },
    {
      label: 'Valor terminal presente',
      value: formatCurrency(props.valuation.summary.terminal_present_value),
      tone: 'cyan',
    },
    {
      label: 'Valor justo por ação',
      value: formatCurrency(props.valuation.summary.fair_value_per_share),
      tone: 'amber',
    },
  ]
})

const yearlyForecasts = computed(() =>
  projectionYearIndexes.value.map((yearIndex) => ({
    yearIndex,
    forecast: props.valuation?.projected_cash_flows[yearIndex] ?? null,
  })),
)

const goBack = () => {
  router.visit('/investiments')
}

const submit = () => {
  form.post(`/investiments/${props.investiment.id}/valuation`)
}
</script>

<template>
  <AppLayout>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
      <div>
        <button
          class="mb-4 inline-flex items-center gap-2 font-medium text-cyan-400 transition hover:text-cyan-300"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4" />
          Voltar
        </button>

        <div class="flex items-center gap-3">
          <Calculator class="h-9 w-9 text-cyan-400" />
          <div>
            <h1 class="text-3xl font-bold text-white">
              Valuation por Fluxo de Caixa Descontado
            </h1>
            <p class="mt-1 max-w-3xl text-sm text-slate-400">
              A distribuição da tela foi pensada com base na lógica de uma planilha: entradas à esquerda, análise no centro e resultado à direita.
            </p>
          </div>
        </div>
      </div>

      <div class="rounded-2xl border border-slate-700 bg-slate-800/80 px-4 py-3 text-sm text-slate-300 shadow-sm">
        <div class="flex items-center gap-2 text-cyan-300">
          <Sparkles class="h-4 w-4" />
          <span class="font-semibold">Organização inspirada no Excel</span>
        </div>
        <p class="mt-1 max-w-md text-slate-400">
          Mantendo os padrões do sistema, mas com a distribuição dos elementos baseada na forma como a planilha é lida.
        </p>
      </div>
    </div>

    <form class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]" @submit.prevent="submit">
      <div class="space-y-6">
        <section class="rounded-2xl border border-slate-700 bg-slate-800 p-6 shadow-sm">
          <div class="mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Realidade atual</p>
              <h2 class="mt-1 text-xl font-semibold text-white">Dados-base do ativo</h2>
            </div>
            <div class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs text-slate-400">
              {{ investiment.category?.name || 'Sem categoria' }}
            </div>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Ticker</label>
              <div class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-white">
                {{ investiment.name }}
              </div>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Preço por ação</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.current_price_per_share" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.current_price_per_share" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Número total de ações</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.total_shares" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.total_shares" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Market cap estimado</label>
              <div class="rounded-lg border border-sky-500/40 bg-sky-500/10 px-3 py-2 font-semibold text-sky-200">
                {{ formatCurrency(marketCap) }}
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-slate-700 bg-slate-800 p-6 shadow-sm">
          <div class="mb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Premissas</p>
            <h2 class="mt-1 text-xl font-semibold text-white">Parâmetros da análise</h2>
          </div>

          <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Lucro líquido atual</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.current_fcf" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.current_fcf" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Taxa de desconto (%)</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.discount_rate" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.discount_rate" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Payout (%)</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.payout" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.payout" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">ROE (%)</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.roe" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.roe" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Crescimento base estimado</label>
              <div class="rounded-lg border border-sky-500/40 bg-sky-500/10 px-3 py-2 font-semibold text-sky-200">
                {{ formatPercent(baseGrowthRate) }}
              </div>
              <p class="mt-2 text-xs text-slate-400">ROE x (1 - payout)</p>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Crescimento na perpetuidade (%)</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.terminal_growth_rate" type="number" step="0.01" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.terminal_growth_rate" />
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/70 p-4">
              <label class="mb-2 block text-sm font-medium text-slate-300">Anos de projeção</label>
              <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                <Input v-model="form.projection_years" type="number" step="1" class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0" />
              </div>
              <InputError :message="form.errors.projection_years" />
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-slate-700 bg-slate-800 p-6 shadow-sm">
          <div class="mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Projeção</p>
              <h2 class="mt-1 text-xl font-semibold text-white">Crescimento por ano</h2>
            </div>
            <div class="rounded-full border border-slate-700 bg-slate-900 px-3 py-1 text-xs text-slate-400">
              {{ projectionYearIndexes.length }} ano(s)
            </div>
          </div>

          <div class="overflow-hidden rounded-2xl border border-slate-700">
            <table class="min-w-full divide-y divide-slate-700 text-sm">
              <thead class="bg-slate-900 text-slate-300">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">Ano</th>
                  <th class="px-4 py-3 text-left font-semibold">Crescimento (%)</th>
                  <th class="px-4 py-3 text-right font-semibold">Lucro projetado</th>
                  <th class="px-4 py-3 text-right font-semibold">Valor presente</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-800 bg-slate-900/60">
                <tr v-for="yearIndex in projectionYearIndexes" :key="yearIndex" class="hover:bg-slate-800/80">
                  <td class="px-4 py-4 font-medium text-slate-200">Ano {{ yearIndex + 1 }}</td>
                  <td class="px-4 py-4">
                    <div class="rounded-lg border border-emerald-500/40 bg-emerald-500/15 px-3 py-2">
                      <Input
                        v-model="form.growth_rates[yearIndex]"
                        type="number"
                        step="0.01"
                        class="border-0 bg-transparent p-0 text-white shadow-none focus-visible:ring-0"
                      />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-right text-slate-200">
                    {{ yearlyForecasts[yearIndex].forecast ? formatCurrency(yearlyForecasts[yearIndex].forecast!.projected_fcf) : '—' }}
                  </td>
                  <td class="px-4 py-4 text-right font-semibold text-cyan-300">
                    {{ yearlyForecasts[yearIndex].forecast ? formatCurrency(yearlyForecasts[yearIndex].forecast!.present_value) : '—' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <aside class="space-y-6">
        <section class="rounded-2xl border border-slate-700 bg-slate-800 p-6 shadow-sm">
          <div class="mb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Resultado</p>
            <h2 class="mt-1 text-xl font-semibold text-white">Resumo do valuation</h2>
          </div>

          <div v-if="valuation" class="grid gap-4">
            <div
              v-for="card in summaryCards"
              :key="card.label"
              class="rounded-xl border border-slate-700 px-4 py-4"
              :class="{
                'bg-sky-500/10': card.tone === 'sky',
                'bg-emerald-500/10': card.tone === 'emerald',
                'bg-cyan-500/10': card.tone === 'cyan',
                'bg-amber-500/10': card.tone === 'amber',
              }"
            >
              <p class="text-xs uppercase tracking-wide text-slate-400">{{ card.label }}</p>
              <p class="mt-2 text-lg font-semibold text-white">{{ card.value }}</p>
            </div>

            <div class="rounded-xl border border-slate-700 bg-slate-900/80 p-4">
              <p class="text-xs uppercase tracking-wide text-slate-400">Margem de segurança</p>
              <p class="mt-2 text-2xl font-bold text-cyan-400">
                {{ formatPercent(valuation.summary.margin_of_safety) }}
              </p>
              <p class="mt-2 text-sm text-slate-400">
                Upside/Downside: {{ formatPercent(valuation.summary.upside) }}
              </p>
            </div>
          </div>

          <div v-else class="rounded-xl border border-dashed border-slate-700 bg-slate-900/40 p-5 text-sm text-slate-400">
            Preencha os dados e clique em calcular para ver o resumo aqui.
          </div>
        </section>

        <section class="rounded-2xl border border-slate-700 bg-slate-800 p-6 shadow-sm">
          <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300">Guia visual</p>
          <h3 class="mt-1 text-lg font-semibold text-white">Como a tela foi organizada</h3>
          <ul class="mt-4 space-y-3 text-sm text-slate-300">
            <li class="rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-3">Entradas principais no bloco superior esquerdo, como numa planilha.</li>
            <li class="rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-3">Premissas logo abaixo, separadas da projeção anual.</li>
            <li class="rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-3">Resumo e resultado ficam em painel lateral à direita.</li>
          </ul>
        </section>
      </aside>

      <div class="xl:col-span-2 flex flex-wrap items-center justify-end gap-3 pt-2">
        <Button
          type="button"
          variant="outline"
          class="border-slate-600 bg-slate-700 text-white hover:bg-slate-600"
          @click="goBack"
        >
          Cancelar
        </Button>
        <Button
          type="submit"
          :disabled="form.processing"
          class="bg-cyan-500 font-semibold text-slate-900 hover:bg-cyan-400"
        >
          {{ form.processing ? 'Calculando...' : 'Calcular valuation' }}
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
