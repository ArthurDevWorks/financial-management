<script setup lang="ts">
import CrudActions from '@/components/CrudActions.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatBadge from '@/components/StatBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Download, Plus, Tags } from 'lucide-vue-next';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Category {
    id: number;
    name: string;
    type: string;
}

defineProps<{
    categories: {
        data: Category[];
        meta: PaginationMeta;
    };
}>();

const typeVariant: Record<string, 'revenue' | 'expense' | 'investment'> = {
    receita: 'revenue',
    despesa: 'expense',
    investimento: 'investment',
};

const typeLabel: Record<string, string> = {
    receita: 'Receita',
    despesa: 'Despesa',
    investimento: 'Investimento',
};

const editCategory = (category: Category) => {
    router.visit(`/categories/${category.id}/edit`);
};

const deleteCategory = (category: Category) => {
    router.delete(`/categories/${category.id}`);
};

const createCategory = () => {
    router.visit('/categories/create');
};
</script>

<template>
    <AppLayout>
        <div class="p-8">
            <PageHeader
                title="Categorias"
                description="Gerencie as categorias de receitas e despesas"
            >
                <template #actions>
                    <Button @click="createCategory">
                        <Plus class="h-4 w-4" />
                        Nova Categoria
                    </Button>
                </template>
            </PageHeader>

            <DataTable
                title="Categorias Cadastradas"
                :description="`${categories.meta?.total || categories.data.length} categoria(s) encontrada(s)`"
                :total="categories.meta?.total"
                :empty="!categories.data.length"
                empty-title="Nenhuma categoria cadastrada"
                empty-description="Crie categorias para organizar suas receitas e despesas"
                :empty-icon="Tags"
            >
                <template #header-actions>
                    <div class="flex items-center gap-2">
                        <a href="/categories/export">
                            <Button
                                variant="outline"
                                size="icon"
                                title="Exportar XLSX"
                            >
                                <Download class="h-4 w-4" />
                            </Button>
                        </a>
                        <SearchInput
                            placeholder="Buscar categorias..."
                            route-name="/categories"
                        />
                    </div>
                </template>

                <template #empty-actions>
                    <Button class="mt-6" @click="createCategory">
                        <Plus class="h-4 w-4" />
                        Nova Categoria
                    </Button>
                </template>

                <template #head>
                    <tr class="border-b border-border">
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Nome
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Tipo
                        </th>
                        <th
                            class="w-52 px-4 py-3 text-center text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Ações
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="category in categories.data"
                        :key="category.id"
                        class="border-b border-border transition-colors hover:bg-surface/50"
                    >
                        <td class="px-4 py-4 font-medium text-foreground">
                            {{ category.name }}
                        </td>
                        <td class="px-4 py-4">
                            <StatBadge
                                :variant="
                                    typeVariant[category.type] || 'default'
                                "
                            >
                                {{ typeLabel[category.type] || category.type }}
                            </StatBadge>
                        </td>
                        <td class="px-4 py-4">
                            <CrudActions
                                delete-confirm-message="Tem certeza que deseja excluir esta categoria?"
                                @edit="editCategory(category)"
                                @delete="deleteCategory(category)"
                            />
                        </td>
                    </tr>
                </template>

                <PaginationLinks
                    v-if="categories.meta"
                    :meta="categories.meta"
                />
            </DataTable>
        </div>
    </AppLayout>
</template>
