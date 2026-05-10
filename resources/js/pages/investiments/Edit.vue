<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, TrendingUp } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Category {
  id: number
  name: string
}

interface Investment {
  id: number
  name: string
  dt_investment: string
  value: number
  type: number
  profitability: number
}

const props = defineProps<{
  investiment: Investment
  categories: Category[]
}>()

const form = useForm({
  name: props.investiment.name,
  value: props.investiment.value.toString(),
  type: props.investiment.type.toString(),
})

const submit = () => {
  form.post(`/investiments/${props.investiment.id}?_method=PUT`)
}

const goBack = () => {
  router.visit('/investiments')
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
        <TrendingUp class="h-8 w-8 text-cyan-400" />
        <h1 class="text-3xl font-bold text-white">
          Editar Investimento
        </h1>
      </div>
      <p class="mt-1 text-slate-300">
        Atualize os dados da posicao cadastrada
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Nome (ticker)
          </label>
          <Input
            v-model="form.name"
            type="text"
            placeholder="Ex: PETR4, IVVB11, HGLG11"
            class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- CATEGORY -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Tipo do ativo
          </label>
          <select
            v-model="form.type"
            class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500"
          >
            <option value="">Selecione o tipo</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <InputError :message="form.errors.type" />
        </div>

        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Cotação do ticker
          </label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">R$</span>
            <Input
              v-model="form.value"
              type="number"
              placeholder="0,00"
              step="0.01"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 pl-8 ml-2"
            />
          </div>
          <InputError :message="form.errors.value" />
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
