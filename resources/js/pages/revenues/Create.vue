<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, TrendingUp } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Account {
  id: number
  name: string
}

interface Category {
  id: number
  name: string
}

defineProps<{
  accounts: Account[]
  categories: Category[]
}>()

const form = useForm({
  account_id: '',
  category_id: '',
  name: '',
  value: '',
  dt_revenue: '',
  description: '',
})

const submit = () => {
  form.post('/revenues')
}

const goBack = () => {
  router.visit('/revenues')
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8">
      <button
        class="mb-4 inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 font-medium transition"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Voltar
      </button>

      <div class="flex items-center gap-3 mb-2">
        <h1 class="text-3xl font-bold text-white">
          Nova Receita
        </h1>
      </div>
      <p class="mt-1 text-slate-400">
        Cadastre uma nova receita no sistema
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- ROW 1 -->
        <div class="grid grid-cols-2 gap-6">
          <!-- ACCOUNT -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Conta
            </label>
            <select
              v-model="form.account_id"
              class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 text-base"
            >
              <option value="" class="bg-slate-700 text-white">Selecione uma conta</option>
              <option
                v-for="account in accounts"
                :key="account.id"
                :value="account.id"
                class="bg-slate-700 text-white"
              >
                {{ account.name }}
              </option>
            </select>
            <InputError :message="form.errors.account_id" />
          </div>

          <!-- CATEGORY -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Categoria
            </label>
            <select
              v-model="form.category_id"
              class="w-full px-4 py-3 rounded-lg bg-slate-700 border border-slate-600 text-white focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 text-base"
            >
              <option value="" class="bg-slate-700 text-white">Selecione uma categoria</option>
              <option
                v-for="category in categories"
                :key="category.id"
                :value="category.id"
                class="bg-slate-700 text-white"
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
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Descrição
            </label>
            <Input
              v-model="form.name"
              type="text"
              placeholder="Ex: Salário"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.name" />
          </div>

          <!-- VALUE -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Valor
            </label>
            <Input
              v-model="form.value"
              type="number"
              placeholder="0.00"
              step="0.01"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.value" />
          </div>
        </div>

        <!-- ROW 3 -->
        <div class="grid grid-cols-2 gap-6">
          <!-- DATE -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Data
            </label>
            <Input
              v-model="form.dt_revenue"
              type="date"
              class="bg-slate-700 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.dt_revenue" />
          </div>

          <!-- DESCRIPTION -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Observação
            </label>
            <Input
              v-model="form.description"
              type="text"
              placeholder="Adicionar nota (opcional)"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.description" />
          </div>
        </div>

        <!-- BUTTONS -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-700">
          <Button
            type="button"
            variant="outline"
            @click="goBack"
            class="bg-slate-700 hover:bg-slate-600 text-white border-slate-600"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="form.processing" class="bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold">
            {{ form.processing ? 'Cadastrando...' : 'Cadastrar Receita' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
