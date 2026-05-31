<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
  title: string;
  description?: string;
  processing?: boolean;
  submitLabel?: string;
  processingLabel?: string;
}

withDefaults(defineProps<Props>(), {
  processing: false,
  submitLabel: 'Salvar',
  processingLabel: 'Salvando...',
});

const emit = defineEmits<{
  (e: 'submit'): void;
  (e: 'cancel'): void;
}>();
</script>

<template>
  <div class="mx-auto max-w-3xl p-8">
    <div class="mb-8">
      <div class="mb-2">
        <button type="button" class="-ml-2 inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground" @click="emit('cancel')">
          <ArrowLeft class="h-4 w-4" />
          Voltar
        </button>
      </div>
      <PageHeader :title="title" :description="description" />
    </div>

    <div class="rounded-xl border border-border bg-card">
      <div class="p-8">
        <form @submit.prevent="emit('submit')" class="space-y-6">
          <slot />

          <div class="flex items-center justify-end gap-3 pt-6 border-t border-border">
            <Button type="button" variant="outline" @click="emit('cancel')">
              Cancelar
            </Button>
            <Button type="submit" variant="default" :disabled="processing">
              {{ processing ? processingLabel : submitLabel }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
