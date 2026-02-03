<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ArrowLeft, Landmark, Upload, CheckCircle } from 'lucide-vue-next'
import { ref } from 'vue'

interface Bank {
  id: number
  name: string
  logo?: string
  logo_url?: string
}

const props = defineProps<{
  bank: Bank
}>()

const logoPreview = ref<string | null>(null)

const form = useForm({
  name: props.bank.name,
  logo: null as File | null,
})

const submit = () => {
  form.post(`/banks/${props.bank.id}?_method=PUT`)
}

const goBack = () => {
  window.history.back()
}

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files?.[0]) {
    form.logo = target.files[0]
    
    const reader = new FileReader()
    reader.onload = (e) => {
      logoPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(target.files[0])
  }
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8">
      <button
        class="mb-4 inline-flex items-center gap-2 text-emerald-600 hover:text-emerald-700 font-medium transition"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Voltar
      </button>

      <div class="flex items-center gap-3 mb-2">
        <h1 class="text-3xl font-bold text-slate-900">
          Editar Banco
        </h1>
      </div>
      <p class="mt-1 text-slate-500">
        Atualize as informações do banco
      </p>
    </div>

    <!-- FORM CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
      <form @submit.prevent="submit" class="space-y-6">
        <!-- NAME -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-3">
            Nome do Banco
          </label>
          <Input
            v-model="form.name"
            type="text"
            placeholder="Ex: Banco do Brasil"
            class="!bg-white text-slate-900"
          />
          <InputError :message="form.errors.name" />
        </div>

        <!-- LOGO UPLOAD -->
        <div>
          <label class="block text-sm font-semibold text-slate-900 mb-3">
            Logo (Opcional)
          </label>
          <div class="relative">
            <input
              type="file"
              accept="image/*"
              class="hidden"
              @change="handleFileSelect"
              id="logo-input"
            />
            
            <!-- CURRENT LOGO -->
            <div v-if="bank.logo_url && !logoPreview" class="mb-4 flex justify-center items-center gap-3 p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl border-2 border-emerald-200">
              <img :src="bank.logo_url" :alt="`Logo ${bank.name}`" class="h-20 object-contain" />
              <span class="text-xs text-emerald-600 font-medium">Logo atual</span>
            </div>
            
            <!-- NEW PREVIEW -->
            <div v-if="logoPreview" class="mb-4 flex justify-center p-6 bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl border-2 border-emerald-200">
              <img :src="logoPreview" alt="Preview" class="h-20 object-contain" />
            </div>
            
            <!-- UPLOAD AREA -->
            <label
              for="logo-input"
              class="flex items-center justify-center w-full px-4 py-8 border-2 border-dashed border-emerald-200 rounded-2xl cursor-pointer hover:border-emerald-400 hover:bg-emerald-50 transition\"
            >
              <div class="text-center">
                <Upload v-if="!logoPreview" class="h-8 w-8 text-emerald-400 mx-auto mb-2" />
                <CheckCircle v-else class="h-8 w-8 text-emerald-600 mx-auto mb-2" />
                <p class="text-slate-600 font-semibold">{{ logoPreview ? 'Nova logo carregada' : 'Clique para alterar a logo' }}</p>
                <p class="text-xs text-slate-500 mt-1">PNG, JPG, JPEG, SVG, WebP (máx. 2MB)</p>
              </div>
            </label>
          </div>
          <InputError :message="form.errors.logo" />
          <p v-if="form.logo" class="text-xs text-emerald-600 mt-2 font-medium">✓ {{ form.logo.name }}</p>
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
          <Button type="submit" :disabled="form.processing" class="bg-emerald-600 text-white">
            {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
