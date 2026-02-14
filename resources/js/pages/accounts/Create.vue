<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, Wallet } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Bank {
  id: number
  name: string
}

defineProps<{
  banks: Bank[]
  accountTypes: Record<string, string>
}>()

const form = useForm({
  bank_id: '',
  type: '',
  agency: '',
  account: '',
  total: '',
})

const submit = () => {
  form.post('/accounts')
}

const goBack = () => {
  router.visit('/accounts')
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
        <Wallet class="h-8 w-8 text-cyan-400" />
        <h1 class="text-3xl font-bold text-white">
          Nova Conta
        </h1>
      </div>
      <p class="mt-1 text-slate-400">
        Cadastre uma nova conta bancária
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-8 shadow-lg">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- BANK -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Banco
          </label>
          <select
            v-model="form.bank_id"
            class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500"
          >
            <option value="">Selecione um banco</option>
            <option v-for="bank in banks" :key="bank.id" :value="bank.id">
              {{ bank.name }}
            </option>
          </select>
          <InputError :message="form.errors.bank_id" />
        </div>

        <!-- TYPE -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Tipo de Conta
          </label>
          <select
            v-model="form.type"
            class="w-full px-4 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500"
          >
            <option value="">Selecione o tipo de conta</option>
            <option v-for="(label, value) in accountTypes" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
          <InputError :message="form.errors.type" />
        </div>

        <!-- AGENCY -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Agência
          </label>
          <Input
            v-model="form.agency"
            type="text"
            placeholder="Ex: 0001"
            class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
          />
          <InputError :message="form.errors.agency" />
        </div>

        <!-- ACCOUNT NUMBER -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Número da Conta
          </label>
          <Input
            v-model="form.account"
            type="text"
            placeholder="Ex: 123456-7"
            class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
          />
          <InputError :message="form.errors.account" />
        </div>

        <!-- TOTAL -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-3">
            Saldo Total
          </label>
          <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">R$</span>
            <Input
              v-model="form.total"
              type="number"
              placeholder="0,00"
              step="0.01"
              class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500 pl-8"
            />
          </div>
          <InputError :message="form.errors.total" />
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
            {{ form.processing ? 'Cadastrando...' : 'Cadastrar Conta' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
