<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';

interface Props {
    title: string;
    description?: string;
    processing?: boolean;
    submitLabel?: string;
    processingLabel?: string;
    dirty?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    processing: false,
    submitLabel: 'Salvar',
    processingLabel: 'Salvando...',
    dirty: false,
});

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'cancel'): void;
}>();

const showUnsavedDialog = defineModel<boolean>('showUnsavedDialog', {
    default: false,
});

function handleCancel() {
    if (props.dirty) {
        showUnsavedDialog.value = true;
    } else {
        emit('cancel');
    }
}

function confirmLeave() {
    showUnsavedDialog.value = false;
    emit('cancel');
}
</script>

<template>
    <div class="mx-auto max-w-3xl p-8">
        <div class="mb-8">
            <div class="mb-2">
                <button
                    type="button"
                    class="-ml-2 inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    @click="handleCancel"
                >
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

                    <div
                        v-if="dirty"
                        class="flex items-center gap-2 rounded-lg bg-accent/10 px-4 py-2 text-xs text-accent"
                    >
                        <div
                            class="h-2 w-2 animate-pulse rounded-full bg-accent"
                        />
                        Alterações não salvas
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-border pt-6"
                    >
                        <Button
                            type="button"
                            variant="outline"
                            @click="handleCancel"
                        >
                            Cancelar
                        </Button>
                        <Button
                            type="submit"
                            variant="default"
                            :disabled="processing"
                        >
                            {{ processing ? processingLabel : submitLabel }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>

        <ConfirmDialog
            v-model:open="showUnsavedDialog"
            title="Alterações não salvas"
            description="Você tem alterações que ainda não foram salvas. Deseja realmente sair?"
            confirm-label="Sair mesmo assim"
            cancel-label="Continuar editando"
            variant="destructive"
            @confirm="confirmLeave"
            @cancel="showUnsavedDialog = false"
        />
    </div>
</template>
