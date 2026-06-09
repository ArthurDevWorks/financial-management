<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { router, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

const showUnsavedDialog = ref(false)

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
  router.visit('/users')
}
</script>

<template>
  <AppLayout>
    <FormPageLayout
      title="Novo Usuário"
      description="Cadastre um usuário"
      :processing="form.processing"
      :dirty="form.isDirty"
      submit-label="Cadastrar Usuário"
      processing-label="Cadastrando..."
      v-model:showUnsavedDialog="showUnsavedDialog"
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div>
          <Label required>Nome Completo</Label>
          <Input v-model="form.name" type="text" placeholder="Nome completo" />
          <InputError :message="form.errors.name" />
        </div>

        <div>
          <Label required>Email</Label>
          <Input v-model="form.email" type="email" placeholder="Email" />
          <InputError :message="form.errors.email" />
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label required>Senha</Label>
            <Input v-model="form.password" type="password" />
            <InputError :message="form.errors.password" />
          </div>
          <div>
            <Label required>Confirmar Senha</Label>
            <Input v-model="form.password_confirmation" type="password" />
            <InputError :message="form.errors.password_confirmation" />
          </div>
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
