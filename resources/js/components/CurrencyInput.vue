<script setup lang="ts">
import { watch } from 'vue'
import { useCurrencyInput } from 'vue-currency-input'
import InputError from '@/components/InputError.vue'

interface Props {
  modelValue: string | number
  error?: string
  placeholder?: string
  disabled?: boolean
  id?: string
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: '0,00',
  disabled: false,
})

const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void
}>()

const currencyOptions = {
  currency: 'BRL',
  locale: 'pt-BR',
  precision: 2,
  autoDecimalDigits: true,
}

const { inputRef, formattedValue, numberValue, setValue } = useCurrencyInput(currencyOptions, false)

watch(
  () => props.modelValue,
  (valorRecebido) => {
    if (valorRecebido === undefined || valorRecebido === null || valorRecebido === '') {
      return
    }

    const valorNumerico = typeof valorRecebido === 'string' ? parseFloat(valorRecebido) || 0 : valorRecebido

    if (numberValue.value !== valorNumerico) {
      setValue(valorNumerico)
    }
  },
  { immediate: true },
)

watch(numberValue, (valorAtual) => {
  if (valorAtual === null || valorAtual === undefined) {
    emit('update:modelValue', '')
    return
  }

  emit('update:modelValue', valorAtual.toFixed(2))
})
</script>

<template>
  <div class="relative mt-1.5">
    <input
      :id="id"
      ref="inputRef"
      :value="formattedValue"
      :placeholder="placeholder"
      :disabled="disabled"
      class="flex h-9 w-full rounded-md border border-border bg-surface px-3 py-1 text-sm text-foreground outline-none transition-all placeholder:text-muted-foreground focus:border-ring focus:ring-[3px] focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
      :class="{ 'border-destructive ring-destructive/20': error }"
    />
    <InputError :message="error" />
  </div>
</template>
