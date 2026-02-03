<script setup lang="ts">
import { ArrowLeft, Users } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/users')
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
          Novo Usuário
        </h1>
      </div>
      <p class="mt-1 text-slate-400">
        Cadastre um novo usuário no sistema
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Nome Completo
          </label>
          <Input
            v-model="form.name"
            type="text"
            placeholder="Ex: João Silva"
            class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- EMAIL -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Email
          </label>
          <Input
            v-model="form.email"
            type="email"
            placeholder="Ex: joao@email.com"
            class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- PASSWORD -->
        <div class="grid grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Senha
            </label>
            <Input
              v-model="form.password"
              type="password"
              placeholder="Mínimo 8 caracteres"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.password" />
          </div>

          <!-- PASSWORD CONFIRMATION -->
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-3">
              Confirmar Senha
            </label>
            <Input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirme a senha"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 text-base"
            />
            <InputError :message="form.errors.password_confirmation" />
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
            {{ form.processing ? 'Cadastrando...' : 'Cadastrar Usuário' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
