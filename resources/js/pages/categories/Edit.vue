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
        class="mb-4 inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium transition"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Voltar
      </button>

      <div class="flex items-center gap-3 mb-2">
        <div class="p-2 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg">
          <Tags class="h-6 w-6 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-slate-900">
          Editar Categoria
        </h1>
      </div>
      <p class="mt-1 text-slate-500 ml-11">
        Modifique os dados da categoria
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- TYPE -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-3">
            Tipo
          </label>
          <select
            v-model="form.type"
            class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 text-base"
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
          <label class="block text-sm font-semibold text-slate-900 mb-3">
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
        <div class="flex justify-end gap-3 pt-6 border-t border-slate-200">
          <Button
            type="button"
            variant="outline"
            @click="goBack"
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="form.processing" class="bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800">
            {{ form.processing ? 'Atualizando...' : 'Atualizar Categoria' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
