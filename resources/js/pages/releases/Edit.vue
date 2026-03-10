<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, ArrowRightLeft } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Account {
  id: number
  account: string
  bank?: { name: string }
}

interface Category {
  id: number
  name: string
}

interface Release {
  id: number
  type: 'revenue' | 'expense'
  account_id: number
  category_id: number
  title: string
  amount: number
  date: string
  description: string | null
}

const props = defineProps<{
  release: Release
  accounts: Account[]
  categories: Category[]
}>()

const form = useForm({
  type: props.release.type,
  account_id: props.release.account_id,
  category_id: props.release.category_id,
  title: props.release.title,
  amount: props.release.amount,
  date: props.release.date,
  description: props.release.description || '',
})

const submit = () => {
  form.put(`/releases/${props.release.id}`)
}

const goBack = () => {
  router.visit('/releases')
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
          Editar Lançamento
        </h1>
      </div>
      <p class="mt-1 text-slate-400">
        Atualize os dados deste lançamento financeiro
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        
        <!-- TIPO DE LANÇAMENTO (Radio buttons) -->
        <div>
           <label class="block text-sm font-semibold text-slate-200 mb-3">
             Tipo de Lançamento
           </label>
           <div class="flex gap-4">
             <label class="flex-1 cursor-pointer">
               <input type="radio" v-model="form.type" value="revenue" class="peer sr-only" />
               <div class="rounded-lg border border-slate-600 bg-slate-700 p-4 text-center hover:bg-slate-600 peer-checked:border-green-500 peer-checked:bg-green-500/10 peer-checked:text-green-400 transition">
                 <span class="font-semibold block">Receita</span>
                 <span class="text-xs opacity-70 mt-1 block">Entrada de dinheiro</span>
               </div>
             </label>
             <label class="flex-1 cursor-pointer">
               <input type="radio" v-model="form.type" value="expense" class="peer sr-only" />
               <div class="rounded-lg border border-slate-600 bg-slate-700 p-4 text-center hover:bg-slate-600 peer-checked:border-red-500 peer-checked:bg-red-500/10 peer-checked:text-red-400 transition">
                 <span class="font-semibold block">Despesa</span>
                 <span class="text-xs opacity-70 mt-1 block">Saída de dinheiro</span>
               </div>
             </label>
           </div>
           <InputError :message="form.errors.type" />
        </div>

        <!-- ROW 1 -->
        <div class="grid grid-cols-2 gap-6 mt-6">
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
                {{ account.bank ? `${account.bank.name} - ${account.account}` : account.account }}
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
          <!-- TITLE -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Descrição
            </label>
            <Input
              v-model="form.title"
              type="text"
              placeholder="Ex: Supermercado"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.title" />
          </div>

          <!-- AMOUNT -->
          <div>
             <label class="block text-sm font-semibold text-slate-200 mb-3">
              Valor
            </label>
            <Input
              v-model="form.amount"
              type="number"
              placeholder="0.00"
              step="0.01"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.amount" />
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
              v-model="form.date"
              type="date"
              class="bg-slate-700 border-slate-600 text-white focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.date" />
          </div>

          <!-- DESCRIPTION (Notes) -->
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
            {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
