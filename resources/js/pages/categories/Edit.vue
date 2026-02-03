<script setup lang="ts">
import { ArrowLeft, Tags } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'

interface Category {
  id: number
  type: string
  name: string
}

const props = defineProps<{
  category: Category
}>()

const form = useForm({
  type: props.category.type,
  name: props.category.name,
})

const submit = () => {
  form.put(`/categories/${props.category.id}`)
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
        class="mb-4 inline-flex items-center gap-2 text-cyan-400 hover:text-cyan-300 font-medium transition"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Voltar
      </button>

      <div class="flex items-center gap-3 mb-2">
        <h1 class="text-3xl font-bold text-white">
          Editar Categoria
        </h1>
      </div>
      <p class="mt-1 text-slate-300">
        Modifique os dados da categoria
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- TYPE -->
        <div>
          <label class="block text-sm font-semibold text-white mb-3">
            Tipo
          </label>
          <select
            v-model="form.type"
            class="w-full px-4 py-3 rounded-lg bg-slate-700 border-slate-600 text-white focus:border-cyan-500 focus:outline-none focus:ring-1 focus:ring-cyan-500 text-base"
          >
            <option value="">Selecione um tipo</option>
            <option value="revenue">Receita</option>
            <option value="expense">Despesa</option>
            <option value="investment">Investimento</option>
          </select>
          <InputError :message="form.errors.type" />
        </div>

        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-white mb-3">
            Nome
          </label>
          <Input
            v-model="form.name"
            type="text"
            placeholder="Ex: Salário, Alimentação"
            class="text-base"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- BUTTONS -->
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-700">
          <Button
            type="button"
            variant="outline"
            @click="goBack"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="form.processing" class="bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold">
            {{ form.processing ? 'Atualizando...' : 'Atualizar Categoria' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
