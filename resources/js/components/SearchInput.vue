<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { router } from '@inertiajs/vue3';
import { Search, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue?: string;
        placeholder?: string;
        routeName?: string;
    }>(),
    {
        placeholder: 'Buscar...',
        routeName: '',
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
}>();

const query = ref(props.modelValue || '');
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(query, (val) => {
    emit('update:modelValue', val);
    if (debounceTimer) clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        if (props.routeName) {
            router.visit(
                `${props.routeName}?search=${encodeURIComponent(val)}`,
                { preserveState: true, replace: true },
            );
        }
    }, 300);
});

function clear() {
    query.value = '';
}
</script>

<template>
    <div class="relative">
        <Search
            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
        />
        <Input
            v-model="query"
            :placeholder="placeholder"
            class="h-9 pr-8 pl-9"
        />
        <button
            v-if="query"
            class="absolute top-1/2 right-2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
            @click="clear"
        >
            <X class="h-4 w-4" />
        </button>
    </div>
</template>
