<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Calculator } from 'lucide-vue-next'
import { computed, watch } from 'vue'

interface Investment {
  id: number
  name: string
  value: number
  average_price: number
  profitability: number
  dt_investment: string
  type: string
  type_label: string
  portfolio_class: string
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

const normalizeNumericString = (value: string | number | null | undefined) => {
  if (value === null || value === undefined || value === '') return ''

  if (typeof value === 'number') {
    return Number.isFinite(value) ? value.toString() : ''
  }

  const textValue = value
    .toString()
    .trim()
    .replace(/[^\d,.-]/g, '')
    .replace(/\s/g, '')
  if (!textValue) return ''

  const lastCommaIndex = textValue.lastIndexOf(',')
  const lastDotIndex = textValue.lastIndexOf('.')
  const commaCount = (textValue.match(/,/g) || []).length
  const dotCount = (textValue.match(/\./g) || []).length

  if (lastCommaIndex > -1 && lastDotIndex > -1) {
    return lastCommaIndex > lastDotIndex
      ? textValue.replace(/\./g, '').replace(',', '.')
      : textValue.replace(/,/g, '')
  }

  if (lastCommaIndex > -1) {
    return commaCount > 1
      ? textValue.replace(/,/g, '')
      : textValue.replace(',', '.')
  }

  if (lastDotIndex > -1) {
    return dotCount > 1
      ? textValue.replace(/\./g, '')
      : textValue
  }

  return textValue
}

const parseNumber = (value: string | number | null | undefined) => {
  const normalizedValue = normalizeNumericString(value)
  if (!normalizedValue) return 0

  const parsed = Number(normalizedValue)
  return Number.isFinite(parsed) ? parsed : 0
}

const normalizeNumericInput = (value: string | number | null | undefined) => {
  return normalizeNumericString(value)
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
const currentCalendarYear = new Date().getFullYear()

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

const projectionCalendarYears = computed(() =>
  Array.from({ length: projectionYearsCount.value }, (_, index) => currentCalendarYear + index),
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
    calendarYear: projectionCalendarYears.value[yearIndex],
    forecast: props.valuation?.projected_cash_flows[yearIndex] ?? null,
  })),
)

const goBack = () => {
  router.visit('/investiments')
}

const submit = () => {
  form
    .transform((data) => ({
      ...data,
      current_fcf: normalizeNumericInput(data.current_fcf),
      discount_rate: normalizeNumericInput(data.discount_rate),
      terminal_growth_rate: normalizeNumericInput(data.terminal_growth_rate),
      projection_years: normalizeNumericInput(data.projection_years),
      total_shares: normalizeNumericInput(data.total_shares),
      payout: normalizeNumericInput(data.payout),
      roe: normalizeNumericInput(data.roe),
      current_price_per_share: normalizeNumericInput(data.current_price_per_share),
      growth_rates: data.growth_rates.map((growthRate) => normalizeNumericInput(growthRate)),
    }))
    .post(`/investiments/${props.investiment.id}/valuation`)
}
</script>

<template>
  <AppLayout>
    <div class="mb-6">
      <div>
        <button
          class="mb-4 inline-flex items-center gap-2 font-medium text-primary transition hover:text-primary"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4" />
          Voltar
        </button>

        <div class="flex items-center gap-3">
          <Calculator class="h-9 w-9 text-primary" />
          <div>
            <h1 class="text-3xl font-bold text-foreground">
              Valuation por Fluxo de Caixa Descontado
            </h1>
            <p class="mt-1 max-w-3xl text-sm text-muted-foreground">
              Preencha as premissas principais e veja o resultado consolidado ao lado.
            </p>
          </div>
        </div>
      </div>
    </div>

    <form class="grid gap-5 xl:grid-cols-[1.1fr_0.9fr]" @submit.prevent="submit">
      <div class="space-y-6">
        <section class="rounded-xl border border-border bg-card p-5">
          <div class="mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Realidade atual</p>
              <h2 class="mt-1 text-xl font-semibold text-foreground">Dados-base do ativo</h2>
            </div>
            <div class="rounded-full border border-border bg-surface px-3 py-1 text-xs text-muted-foreground">
              {{ investiment.type_label || investiment.portfolio_class || 'Não classificado' }}
            </div>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Ticker</label>
              <div class="rounded-lg border border-border bg-surface px-3 py-2 text-foreground">
                {{ investiment.name }}
              </div>
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Preço atual por ação</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.current_price_per_share"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.current_price_per_share" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Número total de ações</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.total_shares"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.total_shares" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Market cap estimado</label>
              <div class="rounded-lg border border-investment/40 bg-investment/10 px-3 py-2 font-semibold text-investment">
                {{ formatCurrency(marketCap) }}
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-border bg-card p-5">
          <div class="mb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Premissas</p>
            <h2 class="mt-1 text-xl font-semibold text-foreground">Parâmetros da análise</h2>
          </div>

          <div class="grid gap-3 md:grid-cols-2">
            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Lucro líquido atual</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.current_fcf"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.current_fcf" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Taxa de desconto (%)</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.discount_rate"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.discount_rate" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Payout (%)</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.payout"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.payout" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">ROE (%)</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.roe"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.roe" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Crescimento base estimado</label>
              <div class="rounded-lg border border-investment/40 bg-investment/10 px-3 py-2 font-semibold text-investment">
                {{ formatPercent(baseGrowthRate) }}
              </div>
              <p class="mt-2 text-xs text-muted-foreground">ROE x (1 - payout)</p>
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Crescimento na perpetuidade (%)</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.terminal_growth_rate"
                  type="text"
                  inputmode="decimal"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.terminal_growth_rate" />
            </div>

            <div class="rounded-lg border border-border bg-surface p-3">
              <label class="mb-2 block text-sm font-medium text-muted-foreground">Anos de projeção</label>
              <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                <Input
                  v-model="form.projection_years"
                  type="text"
                  inputmode="numeric"
                  class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                />
              </div>
              <InputError :message="form.errors.projection_years" />
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-border bg-card p-5">
          <div class="mb-5 flex items-start justify-between gap-4">
            <div>
              <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Projeção</p>
              <h2 class="mt-1 text-xl font-semibold text-foreground">Crescimento por ano</h2>
            </div>
            <div class="rounded-full border border-border bg-surface px-3 py-1 text-xs text-muted-foreground">
              {{ projectionYearIndexes.length }} ano(s) a partir de {{ currentCalendarYear }}
            </div>
          </div>

          <div class="overflow-hidden rounded-xl border border-border">
            <table class="min-w-full divide-y divide-border text-sm">
              <thead class="bg-surface text-muted-foreground">
                <tr>
                  <th class="px-4 py-3 text-left font-semibold">Ano-calendário</th>
                  <th class="px-4 py-3 text-left font-semibold">Crescimento (%)</th>
                  <th class="px-4 py-3 text-right font-semibold">Lucro projetado</th>
                  <th class="px-4 py-3 text-right font-semibold">Valor presente</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border bg-surface">
                <tr v-for="forecast in yearlyForecasts" :key="forecast.yearIndex" class="hover:bg-surface/80">
                  <td class="px-4 py-4 font-medium text-muted-foreground">{{ forecast.calendarYear }}</td>
                  <td class="px-4 py-4">
                    <div class="rounded-lg border border-revenue/40 bg-revenue/15 px-3 py-2">
                      <Input
                        v-model="form.growth_rates[forecast.yearIndex]"
                        type="text"
                        inputmode="decimal"
                        class="border-0 bg-transparent p-0 text-right font-medium tabular-nums text-foreground shadow-none focus-visible:ring-0"
                      />
                    </div>
                  </td>
                  <td class="px-4 py-4 text-right text-muted-foreground">
                    {{ forecast.forecast ? formatCurrency(forecast.forecast.projected_fcf) : '—' }}
                  </td>
                  <td class="px-4 py-4 text-right font-semibold text-primary">
                    {{ forecast.forecast ? formatCurrency(forecast.forecast.present_value) : '—' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>

      <aside class="space-y-6">
        <section class="rounded-xl border border-border bg-card p-5">
          <div class="mb-5">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Resultado</p>
            <h2 class="mt-1 text-xl font-semibold text-foreground">Resumo do valuation</h2>
          </div>

          <div v-if="valuation" class="grid gap-3">
            <div
              v-for="card in summaryCards"
              :key="card.label"
              class="rounded-lg border border-border px-4 py-3"
              :class="{
                'bg-investment/10': card.tone === 'sky',
                'bg-revenue/10': card.tone === 'emerald',
                'bg-primary/10': card.tone === 'cyan',
                'bg-accent/10': card.tone === 'amber',
              }"
            >
              <p class="text-xs uppercase tracking-wide text-muted-foreground">{{ card.label }}</p>
              <p class="mt-2 text-lg font-semibold text-foreground">{{ card.value }}</p>
            </div>

            <div class="rounded-lg border border-border bg-surface p-4">
              <p class="text-xs uppercase tracking-wide text-muted-foreground">Margem de segurança</p>
              <p class="mt-2 text-2xl font-bold text-primary">
                {{ formatPercent(valuation.summary.margin_of_safety) }}
              </p>
              <p class="mt-2 text-sm text-muted-foreground">
                Upside/Downside: {{ formatPercent(valuation.summary.upside) }}
              </p>
            </div>
          </div>

          <div v-else class="rounded-lg border border-dashed border-border bg-surface p-4 text-sm text-muted-foreground">
            Preencha os dados e clique em calcular para ver o resumo aqui.
          </div>
        </section>
      </aside>

      <div class="xl:col-span-2 flex flex-wrap items-center justify-end gap-3 pt-2">
        <Button
          type="button"
          variant="outline"
          @click="goBack"
        >
          Cancelar
        </Button>
        <Button
          type="submit"
          :disabled="form.processing"
        >
          {{ form.processing ? 'Calculando...' : 'Calcular valuation' }}
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
