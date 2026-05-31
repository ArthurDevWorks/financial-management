<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

interface Bank {
  id: number
  name: string
}

interface Account {
  id: number
  bank_id: number
  type: string
  agency: string
  account: string
  total: number
}

const props = defineProps<{
  account: Account
  banks: Bank[]
  accountTypes: Record<string, string>
}>()

const form = useForm({
  bank_id: props.account.bank_id.toString(),
  type: props.account.type,
  agency: props.account.agency,
  account: props.account.account,
  total: props.account.total.toString(),
})

const submit = () => {
  form.post(`/accounts/${props.account.id}?_method=PUT`)
}

const goBack = () => {
  router.visit('/accounts')
}
</script>

<template>
  <AppLayout>
    <FormPageLayout
      title="Editar Conta"
      description="Atualize as informações da conta bancária"
      :processing="form.processing"
      submit-label="Salvar Alterações"
      processing-label="Salvando..."
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label>Banco</Label>
            <select
              v-model="form.bank_id"
              required
              class="h-9 w-full rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
            >
              <option value="" disabled>Selecione um banco</option>
              <option v-for="bank in banks" :key="bank.id" :value="bank.id">
                {{ bank.name }}
              </option>
            </select>
            <InputError :message="form.errors.bank_id" />
          </div>
          <div>
            <Label>Tipo de Conta</Label>
            <select
              v-model="form.type"
              required
              class="h-9 w-full rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
            >
              <option value="" disabled>Selecione o tipo de conta</option>
              <option v-for="(label, value) in accountTypes" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
            <InputError :message="form.errors.type" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <Label>Agência</Label>
            <Input v-model="form.agency" type="text" placeholder="Digite agência" />
            <InputError :message="form.errors.agency" />
          </div>
          <div>
            <Label>Número da Conta</Label>
            <Input v-model="form.account" type="text" placeholder="Digite número da conta" />
            <InputError :message="form.errors.account" />
          </div>
        </div>

        <div>
          <Label>Saldo Inicial</Label>
          <div class="relative">
            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-muted-foreground">R$</span>
            <Input v-model="form.total" type="number" placeholder="0,00" step="0.01" class="pl-8 pr-10" />
          </div>
          <InputError :message="form.errors.total" />
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
