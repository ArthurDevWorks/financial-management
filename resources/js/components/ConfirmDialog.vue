<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { AlertTriangle } from 'lucide-vue-next';

interface Props {
  title?: string;
  description?: string;
  confirmLabel?: string;
  cancelLabel?: string;
  variant?: 'destructive' | 'default';
}

withDefaults(defineProps<Props>(), {
  title: 'Tem certeza?',
  description: 'Esta ação não pode ser desfeita.',
  confirmLabel: 'Confirmar',
  cancelLabel: 'Cancelar',
  variant: 'default',
});

const emit = defineEmits<{
  (e: 'confirm'): void;
  (e: 'cancel'): void;
}>();

const open = defineModel<boolean>('open', { required: true });
</script>

<template>
  <Dialog :open="open" @update:open="emit('cancel')">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-destructive/10">
          <AlertTriangle class="h-6 w-6 text-destructive" />
        </div>
        <DialogTitle class="text-center text-lg">
          {{ title }}
        </DialogTitle>
        <DialogDescription class="text-center">
          {{ description }}
        </DialogDescription>
      </DialogHeader>
      <DialogFooter class="flex-row gap-2 sm:justify-center">
        <Button variant="outline" @click="emit('cancel')">
          {{ cancelLabel }}
        </Button>
        <Button :variant="variant === 'destructive' ? 'destructive' : 'default'" @click="emit('confirm')">
          {{ confirmLabel }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
