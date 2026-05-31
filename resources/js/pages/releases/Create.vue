<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { useForm, router } from '@inertiajs/vue3'
import { computed, watch } from 'vue'

interface Account {
  id: number
  account: string
  bank?: { name: string }
}

interface Category {
  id: number
  name: string
  type: string
}

const props = defineProps<{
  accounts: Account[]
  categories: Category[]
}>()

const form = useForm({
  type: 'expense',
  account_id: '',
  category_id: '',
  title: '',
  amount: '',
  date: '',
  description: '',
})

const submit = () => {
  form.post('/releases')
}

const goBack = () => {
  router.visit('/releases')
}

const filteredCategories = computed(() => {
  const targetType = form.type === 'revenue' ? 'receita' : 'despesa'
  return props.categories.filter(c => c.type === targetType)
})

watch(() => form.type, () => {
  form.category_id = ''
})
</script>

<template>
  <AppLayout>
    <FormPageLayout
      title="Novo Lançamento"
      description="Cadastre uma nova receita ou despesa no sistema"
      :processing="form.processing"
      submit-label="Cadastrar Lançamento"
      processing-label="Cadastrando..."
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div>
          <Label>Tipo de Lançamento</Label>
          <div class="mt-3 flex gap-4">
            <label class="flex-1 cursor-pointer">
              <input type="radio" v-model="form.type" value="revenue" class="peer sr-only" />
              <div class="rounded-lg border border-border bg-surface p-4 text-center transition hover:bg-secondary peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary">
                <span class="block font-semibold">Receita</span>
                <span class="mt-1 block text-xs text-muted-foreground">Entrada de dinheiro</span>
              </div>
            </label>
            <label class="flex-1 cursor-pointer">
              <input type="radio" v-model="form.type" value="expense" class="peer sr-only" />
              <div class="rounded-lg border border-border bg-surface p-4 text-center transition hover:bg-secondary peer-checked:border-destructive peer-checked:bg-destructive/10 peer-checked:text-destructive">
                <span class="block font-semibold">Despesa</span>
                <span class="mt-1 block text-xs text-muted-foreground">Saída de dinheiro</span>
              </div>
            </label>
          </div>
          <InputError :message="form.errors.type" />
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label>Conta</Label>
            <select
              v-model="form.account_id"
              required
              class="h-9 w-full rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
            >
              <option value="" disabled>Selecione uma conta</option>
              <option v-for="account in accounts" :key="account.id" :value="account.id">
                {{ account.bank ? `${account.bank.name} - ${account.account}` : account.account }}
              </option>
            </select>
            <InputError :message="form.errors.account_id" />
          </div>
          <div>
            <Label>Categoria</Label>
            <select
              v-model="form.category_id"
              required
              class="h-9 w-full rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
            >
              <option value="" disabled>Selecione uma categoria</option>
              <option v-for="category in filteredCategories" :key="category.id" :value="category.id">
                {{ category.name }}
              </option>
            </select>
            <InputError :message="form.errors.category_id" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label>Descrição</Label>
            <Input v-model="form.title" type="text" placeholder="Digite descrição" />
            <InputError :message="form.errors.title" />
          </div>
          <div>
            <Label>Valor</Label>
            <div class="relative">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-muted-foreground">R$</span>
              <Input v-model="form.amount" type="number" placeholder="0,00" step="0.01" class="pl-8 pr-10" />
            </div>
            <InputError :message="form.errors.amount" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label>Data</Label>
            <Input v-model="form.date" type="date" />
            <InputError :message="form.errors.date" />
          </div>
          <div>
            <Label>Observação</Label>
            <Input v-model="form.description" type="text" placeholder="Digite observação" />
            <InputError :message="form.errors.description" />
          </div>
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
