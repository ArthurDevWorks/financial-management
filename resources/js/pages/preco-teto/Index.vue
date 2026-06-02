<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Calculator, TrendingUp, ShieldCheck, PiggyBank, ArrowLeft, Banknote, Info } from 'lucide-vue-next';

interface Investment {
  id: number;
  name: string;
  value: number;
}

const props = defineProps<{
  investiment: Investment | null;
  investiments: Investment[];
}>();

const selectedId = ref(props.investiment?.id?.toString() ?? '');

watch(selectedId, (id) => {
  if (id) {
    router.visit(`/preco-teto?investiment_id=${id}`, { preserveState: true, replace: true });
  } else {
    router.visit('/preco-teto', { preserveState: true, replace: true });
  }
});

const dyEsperado = ref('');
const payout = ref('');
const lucroProjetivo = ref('');
const projecaoCrescimento = ref('');
const cotacaoAtual = ref(props.investiment?.value?.toString() ?? '');

watch(() => props.investiment?.value, (val) => {
  if (val && !cotacaoAtual.value) {
    cotacaoAtual.value = val.toString();
  }
});

const dpa = computed(() => {
  const lpa = parseFloat(lucroProjetivo.value);
  const p = parseFloat(payout.value);
  if (!lpa || !p) return 0;
  return lpa * (p / 100);
});

const precoTeto = computed(() => {
  const dy = parseFloat(dyEsperado.value);
  if (!dy || dpa.value === 0) return 0;
  return dpa.value / (dy / 100);
});

const yieldProjetivo = computed(() => {
  const cot = parseFloat(cotacaoAtual.value);
  if (!cot || dpa.value === 0) return 0;
  return (dpa.value / cot) * 100;
});

const margemSeguranca = computed(() => {
  const cot = parseFloat(cotacaoAtual.value);
  if (!cot || precoTeto.value === 0) return 0;
  return ((precoTeto.value - cot) / precoTeto.value) * 100;
});

const margemGaugePercent = computed(() => {
  return Math.min(Math.max(margemSeguranca.value, -50), 80);
});

const margemStatus = computed(() => {
  const m = margemSeguranca.value;
  if (m >= 30) return { label: 'Excelente margem', color: 'bg-revenue', text: 'text-revenue' };
  if (m >= 15) return { label: 'Boa margem', color: 'bg-emerald-500', text: 'text-emerald-500' };
  if (m >= 0) return { label: 'Margem positiva', color: 'bg-primary', text: 'text-primary' };
  if (m >= -15) return { label: 'Margem negativa', color: 'bg-amber-500', text: 'text-amber-500' };
  return { label: 'Risco alto', color: 'bg-destructive', text: 'text-destructive' };
});

const temInputs = computed(() => dyEsperado.value && payout.value && lucroProjetivo.value);

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
};

const formatPercent = (value: number) => {
  return `${value.toFixed(2)}%`;
};

const goBack = () => {
  router.visit('/valuations/create');
};
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <div class="mb-2">
        <button
          type="button"
          class="-ml-2 inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4" />
          Voltar
        </button>
      </div>

      <PageHeader
        title="Preço Teto Projetivo"
        description="Calcule o valor intrínseco com base em dividendos esperados e margem de segurança"
      >
        <template #actions>
          <div class="flex items-center gap-2">
            <Banknote class="h-4 w-4 text-muted-foreground" />
            <select
              v-model="selectedId"
              class="h-9 rounded-md border border-border bg-surface pl-3 pr-8 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20"
            >
              <option value="">Selecione um ativo...</option>
              <option v-for="item in investiments" :key="item.id" :value="item.id">
                {{ item.name }}
              </option>
            </select>
          </div>
        </template>
      </PageHeader>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Inputs -->
        <div class="lg:col-span-2">
          <div class="rounded-xl border border-border bg-card">
            <div class="border-b border-border px-6 py-4">
              <h3 class="text-base font-semibold text-foreground">Parâmetros do Cálculo</h3>
              <p class="text-sm text-muted-foreground">Preencha os campos para calcular o preço teto</p>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                  <div class="flex items-center gap-1.5">
                    <Label>Dividend Yield Esperado (%)</Label>
                    <span class="group relative" title="Retorno esperado em dividendos por ano">
                      <Info class="h-3.5 w-3.5 text-muted-foreground" />
                    </span>
                  </div>
                  <Input
                    v-model="dyEsperado"
                    type="number"
                    step="0.01"
                    placeholder="Ex: 6"
                    class="mt-1.5"
                  />
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <Label>Payout da Empresa (%)</Label>
                    <span class="group relative" title="Percentual do lucro distribuído como dividendos">
                      <Info class="h-3.5 w-3.5 text-muted-foreground" />
                    </span>
                  </div>
                  <Input
                    v-model="payout"
                    type="number"
                    step="0.01"
                    placeholder="Ex: 50"
                    class="mt-1.5"
                  />
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <Label>Lucro Projetivo (LPA) — R$</Label>
                    <span class="group relative" title="Lucro por ação estimado para o próximo ano">
                      <Info class="h-3.5 w-3.5 text-muted-foreground" />
                    </span>
                  </div>
                  <Input
                    v-model="lucroProjetivo"
                    type="number"
                    step="0.01"
                    placeholder="Ex: 8.50"
                    class="mt-1.5"
                  />
                </div>
                <div>
                  <Label>Projeção de Crescimento (%)</Label>
                  <Input
                    v-model="projecaoCrescimento"
                    type="number"
                    step="0.1"
                    placeholder="Ex: 5"
                    class="mt-1.5"
                  />
                </div>
                <div>
                  <Label>Cotação Atual — R$</Label>
                  <Input
                    v-model="cotacaoAtual"
                    type="number"
                    step="0.01"
                    placeholder="Ex: 35.00"
                    class="mt-1.5"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Gauge -->
          <div v-if="temInputs" class="mt-6 rounded-xl border border-border bg-card p-6">
            <div class="mb-2 flex items-center justify-between">
              <span class="text-sm font-semibold text-foreground">Margem de Segurança</span>
              <span :class="`text-sm font-bold ${margemStatus.text}`">{{ formatPercent(margemSeguranca) }}</span>
            </div>
            <div class="relative h-3 overflow-hidden rounded-full bg-muted">
              <div
                class="h-full rounded-full transition-all duration-500 ease-out"
                :class="margemStatus.color"
                :style="{ width: `${((margemGaugePercent + 50) / 130) * 100}%` }"
              />
            </div>
            <div class="mt-1 flex justify-between text-xs text-muted-foreground">
              <span>-50%</span>
              <span>0%</span>
              <span>+80%</span>
            </div>
            <p class="mt-3 text-xs" :class="margemStatus.text">
              {{ margemStatus.label }}
              <template v-if="margemSeguranca > 0">
                — o ativo está sendo negociado {{ margemSeguranca >= 15 ? 'abaixo' : 'ligeiramente abaixo' }} do preço teto
              </template>
              <template v-else>
                — o ativo está sendo negociado acima do preço teto calculado
              </template>
            </p>
          </div>
        </div>

        <!-- Results -->
        <div class="space-y-4">
          <SummaryCard
            label="Preço Teto"
            :value="temInputs ? formatCurrency(precoTeto) : '—'"
            variant="investment"
            :icon="Calculator"
          />

          <SummaryCard
            label="Margem de Segurança"
            :value="temInputs ? formatPercent(margemSeguranca) : '—'"
            :variant="margemSeguranca >= 0 ? 'revenue' : 'expense'"
            :icon="ShieldCheck"
            :trend="temInputs ? parseFloat(margemSeguranca.toFixed(2)) : undefined"
          />

          <SummaryCard
            label="DPA (Dividendo por Ação)"
            :value="temInputs ? formatCurrency(dpa) : '—'"
            variant="default"
            :icon="PiggyBank"
          />

          <SummaryCard
            label="Yield Projetivo"
            :value="temInputs ? formatPercent(yieldProjetivo) : '—'"
            variant="profit"
            :icon="TrendingUp"
            :trend="temInputs ? parseFloat(yieldProjetivo.toFixed(2)) : undefined"
          />
        </div>
      </div>

      <div v-if="!investiments.length" class="mt-6 rounded-xl border border-border bg-card p-6 text-center">
        <p class="text-sm text-muted-foreground">
          Nenhum ativo cadastrado. Crie um investimento primeiro para usar esta ferramenta.
        </p>
      </div>
    </div>
  </AppLayout>
</template>
