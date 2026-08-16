<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { ref, watch } from 'vue';

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
    (e: 'update:modelValue', value: number): void;
}>();

const displayValue = ref('');

const formatter = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
});

function parseBr(val: string | number): number {
    if (typeof val === 'number') return val;
    const cleaned = val.replace(/[R$\s]/g, '');
    if (cleaned.includes(',')) {
        return parseFloat(cleaned.replace(/\./g, '').replace(',', '.')) || 0;
    }
    return parseFloat(cleaned) || 0;
}

function toBrNumber(num: number): string {
    return num.toFixed(2).replace('.', ',');
}

watch(
    () => props.modelValue,
    (val) => {
        if (val === undefined || val === null || val === '') {
            displayValue.value = '';
            return;
        }
        const num = typeof val === 'number' ? val : parseBr(val);
        if (!isNaN(num) && num !== 0) {
            displayValue.value = formatter.format(num);
        }
    },
    { immediate: true },
);

function onFocus(e: FocusEvent) {
    const input = e.target as HTMLInputElement;
    const num = parseBr(input.value);
    if (!isNaN(num) && num !== 0) {
        input.value = toBrNumber(num);
        displayValue.value = toBrNumber(num);
    }
}

function onBlur(e: FocusEvent) {
    const input = e.target as HTMLInputElement;
    const num = parseBr(input.value);
    if (!isNaN(num) && num !== 0) {
        displayValue.value = formatter.format(num);
        emit('update:modelValue', num);
    } else {
        displayValue.value = num === 0 ? formatter.format(0) : '';
        emit('update:modelValue', num);
    }
}

function onInput(e: Event) {
    const input = e.target as HTMLInputElement;
    const raw = input.value.replace(/[^0-9,]/g, '');
    const parts = raw.split(',');
    let cleaned = parts[0];
    if (parts.length > 1) {
        cleaned += ',' + parts.slice(1).join('').slice(0, 2);
    }
    displayValue.value = cleaned;
}
</script>

<template>
    <div class="relative mt-1.5">
        <input
            :id="id"
            :value="displayValue"
            :placeholder="placeholder"
            :disabled="disabled"
            @focus="onFocus"
            @blur="onBlur"
            @input="onInput"
            class="flex h-9 w-full rounded-md border border-border bg-surface px-3 py-1 text-sm text-foreground transition-all outline-none placeholder:text-muted-foreground focus:border-ring focus:ring-[3px] focus:ring-primary/20 disabled:cursor-not-allowed disabled:opacity-50"
            :class="{ 'border-destructive ring-destructive/20': error }"
        />
        <InputError :message="error" />
    </div>
</template>
