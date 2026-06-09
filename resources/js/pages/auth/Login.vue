<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
  status?: string;
  canResetPassword: boolean;
  canRegister: boolean;
}>();
</script>

<template>
  <AuthBase
    title="Bem-vindo de volta"
    description="Acesse sua conta para gerenciar suas finanças"
  >
    <Head title="Log in" />

    <div
      v-if="status"
      class="mb-6 rounded-lg border border-primary/20 bg-primary/10 p-4 text-center text-sm font-medium text-primary"
    >
      {{ status }}
    </div>

    <Form
      v-bind="store.form()"
      :reset-on-success="['password']"
      v-slot="{ errors, processing }"
      class="flex flex-col gap-6"
    >
      <div class="grid gap-6">
        <div class="grid gap-2">
          <Label for="email" required>Email</Label>
          <Input
            id="email"
            type="email"
            name="email"
            required
            autofocus
            :tabindex="1"
            autocomplete="email"
            placeholder="seu@email.com"
          />
          <InputError :message="errors.email" />
        </div>

        <div class="grid gap-2">
          <div class="flex items-center justify-between">
            <Label for="password" required>Senha</Label>
            <TextLink
              v-if="canResetPassword"
              :href="request()"
              class="text-sm font-medium text-primary transition hover:text-primary/80"
              :tabindex="5"
            >
              Esqueceu a senha?
            </TextLink>
          </div>
          <Input
            id="password"
            type="password"
            name="password"
            required
            :tabindex="2"
            autocomplete="current-password"
            placeholder="Sua senha"
          />
          <InputError :message="errors.password" />
        </div>

        <div class="flex items-center justify-between">
          <Label for="remember" class="flex cursor-pointer items-center gap-3">
            <Checkbox id="remember" name="remember" :tabindex="3" />
            <span class="text-sm">Manter-me conectado</span>
          </Label>
        </div>

        <Button
          type="submit"
          class="mt-4 h-11 w-full"
          :tabindex="4"
          :disabled="processing"
          data-test="login-button"
        >
          <LoaderCircle
            v-if="processing"
            class="mr-2 h-4 w-4 animate-spin"
          />
          {{ processing ? 'Acessando...' : 'Acessar Conta' }}
        </Button>
      </div>

      <div
        v-if="canRegister"
        class="text-center text-sm text-muted-foreground"
      >
        Não tem uma conta?
        <TextLink :href="register()" class="font-semibold text-primary transition hover:text-primary/80" :tabindex="5">Cadastrar-se</TextLink>
      </div>
    </Form>
  </AuthBase>
</template>
