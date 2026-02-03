<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, TrendingDown } from 'lucide-vue-next'

interface Account {
  id: number
  name: string
}

interface Category {
  id: number
  name: string
}

interface Expense {
  id: number
  account_id: number
  category_id: number
  name: string
  value: number
  dt_expense: string
  description: string
}

const props = defineProps<{
  expense: Expense
  accounts: Account[]
  categories: Category[]
}>()

const form = useForm({
  account_id: props.expense.account_id,
  category_id: props.expense.category_id,
  name: props.expense.name,
  value: props.expense.value,
  dt_expense: props.expense.dt_expense,
  description: props.expense.description,
})

const submit = () => {
  form.put(`/expenses/${props.expense.id}`)
}

const goBack = () => {
  window.history.back()
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8">
      <button
        class="mb-4 inline-flex items-center gap-2 text-red-600 hover:text-red-700 font-medium transition"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Voltar
      </button>

      <div class="flex items-center gap-3 mb-2">
        <div class="p-2 bg-gradient-to-br from-red-500 to-red-600 rounded-lg">
          <TrendingDown class="h-6 w-6 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-slate-900">
          Editar Despesa
        </h1>
      </div>
      <p class="mt-1 text-slate-500 ml-11">
        Modifique os dados da despesa
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- ROW 1 -->
        <div class="grid grid-cols-2 gap-6">
          <!-- ACCOUNT -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Conta
            </label>
            <select
              v-model="form.account_id"
              class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 text-base"
            >
              <option value="">Selecione uma conta</option>
              <option
                v-for="account in accounts"
                :key="account.id"
                :value="account.id"
              >
                {{ account.name }}
              </option>
            </select>
            <InputError :message="form.errors.account_id" />
          </div>

          <!-- CATEGORY -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Categoria
            </label>
            <select
              v-model="form.category_id"
              class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500 text-base"
            >
              <option value="">Selecione uma categoria</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
              >
                {{ category.name }}
              </option>
            </select>
            <InputError :message="form.errors.category_id" />
          </div>
        </div>

        <!-- ROW 2 -->
        <div class="grid grid-cols-2 gap-6">
          <!-- NAME -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Descrição
            </label>
            <Input
              v-model="form.name"
              type="text"
              placeholder="Ex: Supermercado"
              class="text-base"
            />
            <InputError :message="form.errors.name" />
          </div>

          <!-- VALUE -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Valor
            </label>
            <Input
              v-model="form.value"
              type="number"
              placeholder="0.00"
              step="0.01"
              class="text-base"
            />
            <InputError :message="form.errors.value" />
          </div>
        </div>

        <!-- ROW 3 -->
        <div class="grid grid-cols-2 gap-6">
          <!-- DATE -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Data
            </label>
            <Input
              v-model="form.dt_expense"
              type="date"
              class="text-base"
            />
            <InputError :message="form.errors.dt_expense" />
          </div>

          <!-- DESCRIPTION -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Observação
            </label>
            <Input
              v-model="form.description"
              type="text"
              placeholder="Adicionar nota (opcional)"
              class="text-base"
            />
            <InputError :message="form.errors.description" />
          </div>
        </div>

        <!-- BUTTONS -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
          <Button
            type="button"
            variant="outline"
            @click="goBack"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="form.processing" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800">
            {{ form.processing ? 'Atualizando...' : 'Atualizar Despesa' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
