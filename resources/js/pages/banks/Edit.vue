<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { Upload, CheckCircle } from 'lucide-vue-next'
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

const showUnsavedDialog = ref(false)
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
    <FormPageLayout
      title="Editar Banco"
      description="Atualize as informações do banco"
      :processing="form.processing"
      :dirty="form.isDirty"
      submit-label="Salvar Alterações"
      processing-label="Salvando..."
      v-model:showUnsavedDialog="showUnsavedDialog"
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div>
          <Label>Nome do Banco</Label>
          <Input v-model="form.name" type="text" placeholder="Digite nome do banco" />
          <InputError :message="form.errors.name" />
        </div>

        <div>
          <Label>Logo (Opcional)</Label>
          <input
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileSelect"
            id="logo-input"
          />
          <div v-if="bank.logo_url && !logoPreview" class="mb-4 flex items-center justify-center gap-3 rounded-xl border border-border bg-card p-6">
            <img :src="bank.logo_url" :alt="`Logo ${bank.name}`" class="h-20 object-contain" />
            <span class="text-xs font-medium text-primary">Logo atual</span>
          </div>
          <div v-if="logoPreview" class="mb-4 flex justify-center rounded-xl border border-border bg-card p-6">
            <img :src="logoPreview" alt="Preview" class="h-20 object-contain" />
          </div>
          <label
            for="logo-input"
            class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-border px-4 py-8 transition hover:border-primary hover:bg-primary/5"
          >
            <div class="text-center">
              <Upload v-if="!logoPreview" class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
              <CheckCircle v-else class="mx-auto mb-2 h-8 w-8 text-primary" />
              <p class="font-semibold text-foreground">{{ logoPreview ? 'Nova logo carregada' : 'Clique para alterar a logo' }}</p>
              <p class="mt-1 text-xs text-muted-foreground">PNG, JPG, JPEG, SVG, WebP (máx. 2MB)</p>
            </div>
          </label>
          <InputError :message="form.errors.logo" />
          <p v-if="form.logo" class="mt-2 text-xs font-medium text-primary">✓ {{ form.logo.name }}</p>
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
