<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
    <FormPageLayout
      title="Novo Usuário"
      description="Cadastre um novo usuário no sistema"
      :processing="form.processing"
      submit-label="Cadastrar Usuário"
      processing-label="Cadastrando..."
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div>
          <Label>Nome Completo</Label>
          <Input v-model="form.name" type="text" placeholder="Digite nome completo" />
          <InputError :message="form.errors.name" />
        </div>

        <div>
          <Label>Email</Label>
          <Input v-model="form.email" type="email" placeholder="Digite seu email" />
          <InputError :message="form.errors.email" />
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label>Senha</Label>
            <Input v-model="form.password" type="password" />
            <InputError :message="form.errors.password" />
          </div>
          <div>
            <Label>Confirmar Senha</Label>
            <Input v-model="form.password_confirmation" type="password" />
            <InputError :message="form.errors.password_confirmation" />
          </div>
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
