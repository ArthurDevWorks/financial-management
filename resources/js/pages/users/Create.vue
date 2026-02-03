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
        class="mb-4 inline-flex items-center gap-2 text-purple-600 hover:text-purple-700 font-medium transition"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Voltar
      </button>

      <div class="flex items-center gap-3 mb-2">
        <div class="p-2 bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg">
          <Users class="h-6 w-6 text-white" />
        </div>
        <h1 class="text-3xl font-bold text-slate-900">
          Novo Usuário
        </h1>
      </div>
      <p class="mt-1 text-slate-500 ml-11">
        Cadastre um novo usuário no sistema
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-3">
            Nome Completo
          </label>
          <Input
            v-model="form.name"
            type="text"
            placeholder="Ex: João Silva"
            class="text-base"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- EMAIL -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-3">
            Email
          </label>
          <Input
            v-model="form.email"
            type="email"
            placeholder="Ex: joao@email.com"
            class="text-base"
          />
          <InputError :message="form.errors.email" />
        </div>

        <!-- PASSWORD -->
        <div class="grid grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Senha
            </label>
            <Input
              v-model="form.password"
              type="password"
              placeholder="Mínimo 8 caracteres"
              class="text-base"
            />
            <InputError :message="form.errors.password" />
          </div>

          <!-- PASSWORD CONFIRMATION -->
          <div>
            <label class="block text-sm font-semibold text-slate-900 mb-3">
              Confirmar Senha
            </label>
            <Input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirme a senha"
              class="text-base"
            />
            <InputError :message="form.errors.password_confirmation" />
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
          <Button type="submit" :disabled="form.processing" class="bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800">
            {{ form.processing ? 'Cadastrando...' : 'Cadastrar Usuário' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
