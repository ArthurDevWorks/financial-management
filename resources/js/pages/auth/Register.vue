<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
  <AuthBase
    title="Crie sua conta"
    description="Comece a gerenciar suas finanças agora mesmo"
  >
    <Head title="Register" />

    <Form
      v-bind="store.form()"
      :reset-on-success="['password', 'password_confirmation']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-6">
        <div class="grid gap-2">
          <Label for="name" required>Nome Completo</Label>
          <Input
            id="name"
            type="text"
            required
            autofocus
            :tabindex="1"
            autocomplete="name"
            name="name"
            placeholder="Seu nome"
          />
          <InputError :message="errors.name" />
        </div>

        <div class="grid gap-2">
          <Label for="email" required>Email</Label>
          <Input
            id="email"
            type="email"
            required
            :tabindex="2"
            autocomplete="email"
            name="email"
            placeholder="seu@email.com"
          />
          <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
          <Label for="password" required>Senha</Label>
          <Input
            id="password"
            type="password"
            required
            :tabindex="3"
            autocomplete="new-password"
            name="password"
            placeholder="Mínimo 8 caracteres"
          />
          <InputError :message="errors.password" />
        </div>

        <div class="grid gap-2">
          <Label for="password_confirmation" required>Confirmar Senha</Label>
          <Input
            id="password_confirmation"
            type="password"
            required
            :tabindex="4"
            autocomplete="new-password"
            name="password_confirmation"
            placeholder="Repita a senha"
          />
          <InputError :message="errors.password_confirmation" />
        </div>

        <Button
          type="submit"
          class="mt-4 h-11 w-full"
          :tabindex="5"
          :disabled="processing"
        >
          <LoaderCircle
            v-if="processing"
            class="mr-2 h-4 w-4 animate-spin"
          />
          {{ processing ? 'Criando conta...' : 'Criar Conta' }}
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        Já tem uma conta?
        <TextLink :href="login()" class="font-semibold text-primary transition hover:text-primary/80" :tabindex="6">Acessar</TextLink>
      </div>
    </Form>
  </AuthBase>
</template>
