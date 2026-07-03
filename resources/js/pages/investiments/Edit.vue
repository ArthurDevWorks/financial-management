<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import FormPageLayout from '@/components/FormPageLayout.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface AssetTypeOption {
  value: string;
  label: string;
  portfolio_class: string;
  is_fixed_income: boolean;
}

interface Investment {
  id: number;
  name: string;
  dt_investment: string | null;
  type: string;
  current_balance: number;
  logo_url: string | null;
}

const props = defineProps<{
  investiment: Investment;
  assetTypes: AssetTypeOption[];
}>();

const showUnsavedDialog = ref(false);

const assetTypesOptions = computed(() => props.assetTypes.filter((assetType) => assetType.value === 'acoes'));

const form = useForm({
  name: props.investiment.name,
  type: props.investiment.type ?? assetTypesOptions.value[0]?.value ?? '',
  current_balance: props.investiment.current_balance?.toString() ?? '',
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
            <Label required>Nome (Ticker)</Label>
            <div class="flex items-center gap-3">
              <img
                v-if="investiment.logo_url"
                :src="investiment.logo_url"
                :alt="investiment.name"
                class="h-8 w-8 rounded-full object-contain"
              />
              <Input v-model="form.name" type="text" placeholder="Ex.: PETR4, HGLG11" class="flex-1" />
            </div>
            <InputError :message="form.errors.name" />
          </div>

          <div>
            <Label required>Categoria</Label>
            <select
              v-model="form.type"
              required
              class="h-9 w-full rounded-md border border-border bg-surface py-1 pl-3 pr-10 text-sm text-foreground outline-none transition-all [color-scheme:dark] focus:border-ring focus:ring-[3px] focus:ring-primary/20"
            >
              <option value="" disabled>Selecione a categoria</option>
              <option v-for="assetType in assetTypesOptions" :key="assetType.value" :value="assetType.value">
                {{ assetType.label }}
              </option>
            </select>
            <InputError :message="form.errors.type" />
          </div>
        </div>

        <div>
          <Label required>Valor da Cotação</Label>
          <CurrencyInput v-model="form.current_balance" :error="form.errors.current_balance" placeholder="3.800,00" />
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
