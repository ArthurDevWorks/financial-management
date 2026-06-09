<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ArrowLeft, ChartNoAxesCombined, Calculator, TrendingUp } from 'lucide-vue-next';

interface Investment {
  id: number;
  name: string;
}

defineProps<{
  investiments: Investment[];
}>();

type ValuationMethod = 'dcf' | 'preco-teto';

const form = useForm<{
  investiment_id: string;
  method: ValuationMethod | '';
}>({
  investiment_id: '',
  method: '',
});

const submit = () => {
  if (form.method === 'dcf') {
    router.visit(`/investiments/${form.investiment_id}`);
  } else if (form.method === 'preco-teto') {
    router.visit(`/preco-teto?investiment_id=${form.investiment_id}`);
  }
};

const goBack = () => {
  router.visit('/valuations');
};

const methods: { value: ValuationMethod; label: string; description: string; icon: any }[] = [
  {
    value: 'dcf',
    label: 'Fluxo de Caixa Descontado',
    description: 'Calcula o valor presente dos fluxos de caixa futuros projetados, descontados por uma taxa que reflete o risco do ativo.',
    icon: Calculator,
  },
  {
    value: 'preco-teto',
    label: 'Preço Teto Projeto',
    description: 'Determina o preço máximo de compra com base em múltiplos e margem de segurança sobre o valor intrínseco estimado.',
    icon: TrendingUp,
  },
];
</script>

<template>
  <AppLayout>
    <div class="mx-auto max-w-3xl p-8">
      <div class="mb-8">
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
          title="Nova Valuation"
          description="Selecione o ativo e o método de cálculo"
        />
      </div>

      <div v-if="!investiments.length" class="rounded-xl border border-border bg-card p-8 text-center">
        <ChartNoAxesCombined class="mx-auto mb-4 h-16 w-16 text-muted-foreground opacity-15" />
        <p class="font-medium text-foreground">Nenhum investimento cadastrado</p>
        <p class="mt-1 text-sm text-muted-foreground">
          Cadastre um investimento primeiro para poder realizar valuations.
        </p>
        <Button class="mt-6" @click="router.visit('/investiments/create')">
          Novo Investimento
        </Button>
      </div>

      <form v-else @submit.prevent="submit" class="space-y-6">
        <SectionCard title="Selecione o Ativo">
          <div>
            <Label required>Ativo</Label>
            <select
              v-model="form.investiment_id"
              required
              class="mt-1.5 h-9 w-full rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
            >
              <option value="" disabled>Selecione um ativo</option>
              <option v-for="item in investiments" :key="item.id" :value="item.id">
                {{ item.name }}
              </option>
            </select>
            <InputError :message="form.errors.investiment_id" />
          </div>
        </SectionCard>

        <SectionCard title="Método de Cálculo">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <button
              v-for="method in methods"
              :key="method.value"
              type="button"
              class="flex flex-col items-start gap-3 rounded-lg border p-5 text-left transition-all"
              :class="form.method === method.value
                ? 'border-primary bg-primary/5 ring-1 ring-primary'
                : 'border-border hover:border-border/80 hover:bg-surface/50'"
              @click="form.method = method.value"
            >
              <component :is="method.icon" class="h-6 w-6" :class="form.method === method.value ? 'text-primary' : 'text-muted-foreground'" />
              <div>
                <p class="font-semibold text-foreground" :class="form.method === method.value ? 'text-primary' : ''">
                  {{ method.label }}
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                  {{ method.description }}
                </p>
              </div>
            </button>
          </div>
          <InputError :message="form.errors.method" />
        </SectionCard>

        <div class="flex items-center justify-end gap-3">
          <Button type="button" variant="outline" @click="goBack">
            Cancelar
          </Button>
          <Button type="submit" variant="default" :disabled="!form.investiment_id || !form.method">
            Iniciar Cálculo
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
