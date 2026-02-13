<script setup lang="ts">
import { ArrowLeft, Tags } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'

interface TypeOption {
  [key: string]: string
}

defineProps<{
  types: TypeOption
}>()

const form = useForm({
  type: '',
  name: '',
})

const submit = () => {
  form.post('/categories')
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
        <Tags class="h-8 w-8 text-cyan-400" />
        <h1 class="text-3xl font-bold text-white">
          Nova Categoria
        </h1>
      </div>
      <p class="mt-1 text-slate-400">
        Cadastre uma nova categoria no sistema
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- TYPE -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Tipo de Categoria
          </label>
          <select
            v-model="form.type"
            class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500"
          >
            <option value="">Selecione um tipo</option>
            <option v-for="(label, value) in types" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
          <InputError :message="form.errors.type" />
        </div>

        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Nome da Categoria
          </label>
          <Input
            v-model="form.name"
            type="text"
            placeholder="Ex: Salário, Alimentação, Ações"
            class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
          />
          <InputError :message="form.errors.name" />
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
            {{ form.processing ? 'Cadastrando...' : 'Cadastrar Categoria' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
