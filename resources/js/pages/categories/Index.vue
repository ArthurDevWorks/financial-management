<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import StatBadge from '@/components/StatBadge.vue';
import CrudActions from '@/components/CrudActions.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Plus, Tags } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Category {
  id: number;
  name: string;
  type: string;
}

defineProps<{
  categories: {
    data: Category[];
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
  if (confirm(`Deseja excluir a categoria "${category.name}"?`)) {
    router.delete(`/categories/${category.id}`);
  }
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

      <SectionCard
        title="Categorias Cadastradas"
        :description="`${categories.data.length} categoria(s) encontrada(s)`"
      >
        <div v-if="categories.data.length" class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Nome
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Tipo
                </th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground w-52">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="category in categories.data"
                :key="category.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <td class="px-4 py-4 font-medium text-foreground">
                  {{ category.name }}
                </td>
                <td class="px-4 py-4">
                  <StatBadge :variant="typeVariant[category.type] || 'default'">
                    {{ typeLabel[category.type] || category.type }}
                  </StatBadge>
                </td>
                <td class="px-4 py-4">
                  <CrudActions
                    @edit="editCategory(category)"
                    @delete="deleteCategory(category)"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else>
          <EmptyState
            :icon="Tags"
            title="Nenhuma categoria cadastrada"
            description="Crie categorias para organizar suas receitas e despesas"
          >
            <template #actions>
              <Button @click="createCategory">
                <Plus class="h-4 w-4" />
                Nova Categoria
              </Button>
            </template>
          </EmptyState>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>
