<script setup lang="ts">
import { useCurrencyInput } from 'vue-currency-input';
import { ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';

interface Props {
  modelValue: string | number;
  error?: string;
  placeholder?: string;
  disabled?: boolean;
  id?: string;
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: '0,00',
  disabled: false,
});

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
}>();

const currencyOptions = {
  currency: 'BRL',
  locale: 'pt-BR',
  precision: 2,
  autoDecimalDigits: true,
  valueScaling: 'precision',
};

const inputRef = ref<HTMLInputElement | null>(null);

const { formattedValue, setValue } = useCurrencyInput(currencyOptions);

watch(
  () => props.modelValue,
  (val) => {
    if (val !== undefined && val !== null && val !== '') {
      const num = typeof val === 'string' ? parseFloat(val) || 0 : val;
      if (num !== parseFloat(formattedValue.value?.replace(/[^\d,-]/g, '') || '0')) {
        setValue(num);
      }
    }
  },
);

function onInput() {
  const raw = formattedValue.value;
  if (raw) {
    const numeric = parseFloat(raw.replace(/[^\d,-]/g, '').replace(',', '.')) || 0;
    emit('update:modelValue', numeric.toFixed(2));
  }
}
</script>

<template>
  <div class="relative mt-1.5">
    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm text-muted-foreground">R$</span>
      <input
        :id="id"
        ref="inputRef"
        :value="formattedValue"
        :placeholder="placeholder"
        :disabled="disabled"
        class="flex h-9 w-full rounded-md border border-border bg-surface pl-8 pr-3 py-1 text-sm text-foreground outline-none transition-all placeholder:text-muted-foreground focus:border-ring focus:ring-[3px] focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
        :class="{ 'border-destructive ring-destructive/20': error }"
        @input="onInput"
      />
    </div>
    <InputError :message="error" />
  </div>
</template>
