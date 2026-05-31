<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { computed, ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Calculator, TrendingUp, ShieldCheck, PiggyBank } from 'lucide-vue-next';

interface Investment {
  id: number;
  name: string;
  value: number;
}

const props = defineProps<{
  investiment: Investment | null;
}>();

const dyEsperado = ref('');
const payout = ref('');
const lucroProjetivo = ref('');
const projecaoCrescimento = ref('');
const cotacaoAtual = ref(props.investiment?.value?.toString() ?? '');

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
        :description="investiment ? `Cálculo para ${investiment.name}` : 'Calcule o preço teto do ativo'"
      />

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Inputs -->
        <div class="lg:col-span-2">
          <SectionCard title="Parâmetros do Cálculo">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div>
                <Label>Dividend Yield Esperado (%)</Label>
                <Input
                  v-model="dyEsperado"
                  type="number"
                  step="0.01"
                  placeholder="Ex: 6"
                  class="mt-1.5"
                />
              </div>
              <div>
                <Label>Payout da Empresa (%)</Label>
                <Input
                  v-model="payout"
                  type="number"
                  step="0.01"
                  placeholder="Ex: 50"
                  class="mt-1.5"
                />
              </div>
              <div>
                <Label>Lucro Projetivo (LPA) — R$</Label>
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
          </SectionCard>
        </div>

        <!-- Results -->
        <div class="space-y-4">
          <SummaryCard
            label="Preço Teto"
            :value="formatCurrency(precoTeto)"
            variant="investment"
            :icon="Calculator"
          />

          <SummaryCard
            label="Margem de Segurança"
            :value="formatPercent(margemSeguranca)"
            :variant="margemSeguranca >= 0 ? 'revenue' : 'expense'"
            :icon="ShieldCheck"
            :trend="parseFloat(margemSeguranca.toFixed(2))"
          />

          <SummaryCard
            label="DPA (Dividendo por Ação)"
            :value="formatCurrency(dpa)"
            variant="default"
            :icon="PiggyBank"
          />

          <SummaryCard
            label="Yield Projetivo"
            :value="formatPercent(yieldProjetivo)"
            variant="profit"
            :icon="TrendingUp"
            :trend="parseFloat(yieldProjetivo.toFixed(2))"
          />
        </div>
      </div>

      <!-- Info quando não há investimento -->
      <div v-if="!investiment" class="mt-6 rounded-xl border border-border bg-card p-6 text-center">
        <p class="text-sm text-muted-foreground">
          Os valores calculados não serão salvos. Selecione um ativo pela opção
          <strong>Nova Valuation</strong> no menu Valuations para associar o cálculo a um investimento.
        </p>
      </div>
    </div>
  </AppLayout>
</template>
