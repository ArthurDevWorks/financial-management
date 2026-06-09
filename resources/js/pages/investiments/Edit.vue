<script setup lang="ts">
import FormPageLayout from '@/components/FormPageLayout.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface AssetTypeOption {
  value: string;
  label: string;
  portfolio_class: string;
  is_fixed_income: boolean;
}

interface Option {
  value: string;
  label: string;
}

interface Investment {
  id: number;
  name: string;
  dt_investment: string | null;
  type: string;
  quantity: number;
  average_price: number;
  current_balance: number;
  profitability_type: string | null;
  indexer: string | null;
  contracted_rate: number | null;
  maturity_date: string | null;
  liquidity: string | null;
}

const props = defineProps<{
  investiment: Investment;
  assetTypes: AssetTypeOption[];
  fixedIncomeProfitabilityTypes: Option[];
  fixedIncomeIndexers: Option[];
}>();

const showUnsavedDialog = ref(false);

const form = useForm({
  name: props.investiment.name,
  type: props.investiment.type ?? '',
  quantity: props.investiment.quantity?.toString() ?? '',
  average_price: props.investiment.average_price?.toString() ?? '',
  current_balance: props.investiment.current_balance?.toString() ?? '',
  profitability_type: props.investiment.profitability_type ?? '',
  indexer: props.investiment.indexer ?? '',
  contracted_rate: props.investiment.contracted_rate?.toString() ?? '',
  maturity_date: props.investiment.maturity_date ?? '',
  liquidity: props.investiment.liquidity ?? '',
});

const selectedAssetType = computed(() => props.assetTypes.find((assetType) => assetType.value === form.type));
const isFixedIncome = computed(() => selectedAssetType.value?.is_fixed_income ?? false);

watch(isFixedIncome, (fixedIncome) => {
  if (!fixedIncome) {
    form.profitability_type = '';
    form.indexer = '';
    form.contracted_rate = '';
    form.maturity_date = '';
    form.liquidity = '';
  }
});

const submit = () => {
  form.post(`/investiments/${props.investiment.id}?_method=PUT`);
};

const goBack = () => {
  router.visit('/investiments');
};
</script>

<template>
  <AppLayout>
    <FormPageLayout
      title="Editar Investimento"
      description="Atualize a posição e as características do ativo"
      :processing="form.processing"
      :dirty="form.isDirty"
      submit-label="Salvar Alterações"
      processing-label="Salvando..."
      v-model:showUnsavedDialog="showUnsavedDialog"
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div class="grid gap-6 md:grid-cols-2">
          <div>
            <Label required>Ativo / Ticker</Label>
            <Input v-model="form.name" type="text" placeholder="Ex.: PETR4, HGLG11, CDB Banco X" />
            <InputError :message="form.errors.name" />
          </div>

          <div>
            <Label required>Tipo do ativo</Label>
            <select
              v-model="form.type"
              required
              class="h-9 w-full rounded-md border border-border bg-surface py-1 pl-3 pr-10 text-sm text-foreground outline-none transition-all [color-scheme:dark] focus:border-ring focus:ring-[3px] focus:ring-primary/20"
            >
              <option value="" disabled>Selecione o tipo</option>
              <option v-for="assetType in assetTypes" :key="assetType.value" :value="assetType.value">
                {{ assetType.label }} · {{ assetType.portfolio_class }}
              </option>
            </select>
            <InputError :message="form.errors.type" />
          </div>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <div>
            <Label required>Quantidade</Label>
            <Input v-model="form.quantity" type="number" min="0" step="0.00000001" placeholder="100" />
            <InputError :message="form.errors.quantity" />
          </div>

          <div>
            <Label required>Preço Médio</Label>
            <CurrencyInput v-model="form.average_price" :error="form.errors.average_price" placeholder="32,50" />
          </div>

          <div>
            <Label>Saldo Atual</Label>
            <CurrencyInput v-model="form.current_balance" :error="form.errors.current_balance" placeholder="3.800,00" />
          </div>
        </div>

        <section v-if="isFixedIncome" class="rounded-xl border border-border bg-surface p-5">
          <div class="mb-4">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Renda Fixa</p>
            <h2 class="mt-1 text-lg font-semibold text-foreground">Condições contratadas</h2>
          </div>

          <div class="grid gap-6 md:grid-cols-2">
            <div>
              <Label>Tipo de Rentabilidade</Label>
              <select
                v-model="form.profitability_type"
                required
                class="h-9 w-full rounded-md border border-border bg-surface py-1 pl-3 pr-10 text-sm text-foreground outline-none transition-all [color-scheme:dark] focus:border-ring focus:ring-[3px] focus:ring-primary/20"
              >
                <option value="" disabled>Selecione</option>
                <option v-for="type in fixedIncomeProfitabilityTypes" :key="type.value" :value="type.value">
                  {{ type.label }}
                </option>
              </select>
              <InputError :message="form.errors.profitability_type" />
            </div>

            <div>
              <Label>Indexador</Label>
              <select
                v-model="form.indexer"
                required
                class="h-9 w-full rounded-md border border-border bg-surface py-1 pl-3 pr-10 text-sm text-foreground outline-none transition-all [color-scheme:dark] focus:border-ring focus:ring-[3px] focus:ring-primary/20"
              >
                <option value="" disabled>Selecione</option>
                <option v-for="indexer in fixedIncomeIndexers" :key="indexer.value" :value="indexer.value">
                  {{ indexer.label }}
                </option>
              </select>
              <InputError :message="form.errors.indexer" />
            </div>

            <div>
              <Label>Taxa Contratada</Label>
              <Input v-model="form.contracted_rate" type="number" min="0" step="0.0001" placeholder="Ex.: 110, 95, 6 ou 13" />
              <InputError :message="form.errors.contracted_rate" />
            </div>

            <div>
              <Label>Vencimento</Label>
              <Input v-model="form.maturity_date" type="date" />
              <InputError :message="form.errors.maturity_date" />
            </div>

            <div class="md:col-span-2">
              <Label>Liquidez</Label>
              <Input v-model="form.liquidity" type="text" placeholder="Ex.: D+0, D+1, no vencimento" />
              <InputError :message="form.errors.liquidity" />
            </div>
          </div>
        </section>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
