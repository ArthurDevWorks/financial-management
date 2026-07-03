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

const props = defineProps<{
  assetTypes: AssetTypeOption[];
}>();

const showUnsavedDialog = ref(false);
const logoUrl = ref<string | null>(null);
const fetchingQuote = ref(false);

const assetTypesOptions = computed(() => props.assetTypes.filter((assetType) => assetType.value === 'acoes'));

const form = useForm({
  name: '',
  type: assetTypesOptions.value[0]?.value ?? '',
  current_balance: '',
});

const fetchQuote = async () => {
  const symbol = form.name.trim().toUpperCase();
  if (!symbol) return;

  fetchingQuote.value = true;

  try {
    const response = await fetch(`/api/quote?symbol=${encodeURIComponent(symbol)}`);
    if (!response.ok) return;

    const data = await response.json();
    if (data.price) {
      form.current_balance = data.price.toString();
    }
    logoUrl.value = data.logourl ?? null;
  } catch {
    // silently ignore
  } finally {
    fetchingQuote.value = false;
  }
};

const submit = () => {
  form.post('/investiments');
};

const goBack = () => {
  router.visit('/investiments');
};
</script>

<template>
  <AppLayout>
    <FormPageLayout
      title="Novo Investimento"
      description="Registre uma posição da carteira por tipo de ativo"
      :processing="form.processing"
      :dirty="form.isDirty"
      submit-label="Cadastrar Investimento"
      processing-label="Cadastrando..."
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
                v-if="logoUrl"
                :src="logoUrl"
                alt=""
                class="h-8 w-8 rounded-full object-contain"
              />
              <div
                v-else-if="fetchingQuote"
                class="flex h-8 w-8 items-center justify-center rounded-full bg-muted"
              >
                <span class="text-xs text-muted-foreground animate-pulse">...</span>
              </div>
              <Input
                v-model="form.name"
                type="text"
                placeholder="Ex.: PETR4"
                class="flex-1"
                @blur="fetchQuote"
              />
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
