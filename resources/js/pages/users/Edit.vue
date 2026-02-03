<script setup lang="ts">
import { ArrowLeft, Users } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'

interface User {
  id: number
  name: string
  email: string
}

const props = defineProps<{
  user: User
}>()

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.put(`/users/${props.user.id}`)
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
          Editar Usuário
        </h1>
      </div>
      <p class="mt-1 text-slate-300">
        Modifique os dados do usuário
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-white mb-3">
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
          <label class="block text-sm font-semibold text-white mb-3">
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
            <label class="block text-sm font-semibold text-white mb-3">
              Nova Senha
            </label>
            <Input
              v-model="form.password"
              type="password"
              placeholder="Deixe em branco para manter a atual"
              class="text-base"
            />
            <InputError :message="form.errors.password" />
          </div>

          <!-- PASSWORD CONFIRMATION -->
          <div>
            <label class="block text-sm font-semibold text-white mb-3">
              Confirmar Senha
            </label>
            <Input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirme a nova senha"
              class="text-base"
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
          >
            Cancelar
          </Button>
          <Button type="submit" :disabled="form.processing" class="bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold">
            {{ form.processing ? 'Atualizando...' : 'Atualizar Usuário' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
