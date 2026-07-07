<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    editUrl?: string;
    deleteConfirmMessage?: string;
    resourceName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    deleteConfirmMessage: 'Tem certeza que deseja excluir este item?',
    resourceName: 'item',
});

interface Emits {
    (e: 'edit'): void;
    (e: 'delete'): void;
}

const emit = defineEmits<Emits>();

const showDeleteDialog = ref(false);
</script>

<template>
    <div class="flex items-center justify-center gap-2">
        <slot />
        <Button
            variant="secondary"
            size="sm"
            class="gap-1.5"
            @click="emit('edit')"
        >
            <Pencil class="h-3.5 w-3.5" />
            Editar
        </Button>
        <Button
            variant="destructive"
            size="sm"
            class="gap-1.5"
            @click="showDeleteDialog = true"
        >
            <Trash2 class="h-3.5 w-3.5" />
            Excluir
        </Button>
        <ConfirmDialog
            v-model:open="showDeleteDialog"
            :description="props.deleteConfirmMessage"
            confirm-label="Sim, excluir"
            variant="destructive"
            @confirm="
                emit('delete');
                showDeleteDialog = false;
            "
            @cancel="showDeleteDialog = false"
        />
    </div>
</template>
