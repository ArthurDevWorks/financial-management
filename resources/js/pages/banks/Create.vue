<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { Upload, CheckCircle } from 'lucide-vue-next'
import { ref } from 'vue'

const showUnsavedDialog = ref(false)
const logoPreview = ref<string | null>(null)

const form = useForm({
  name: '',
  logo: null as File | null,
})

const submit = () => {
  form.post('/banks')
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
      title="Novo Banco"
      description="Cadastre um novo banco para gerenciar suas contas"
      :processing="form.processing"
      :dirty="form.isDirty"
      submit-label="Cadastrar Banco"
      processing-label="Cadastrando..."
      v-model:showUnsavedDialog="showUnsavedDialog"
      @submit="submit"
      @cancel="goBack"
    >
      <div class="space-y-6">
        <div>
          <Label>Nome do Banco</Label>
          <Input v-model="form.name" type="text" placeholder="Digite o nome do banco" />
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
          <div v-if="logoPreview" class="mb-4 flex justify-center p-6 bg-card rounded-xl border border-border">
            <img :src="logoPreview" alt="Preview" class="h-20 object-contain" />
          </div>
          <label
            for="logo-input"
            class="flex cursor-pointer items-center justify-center rounded-xl border-2 border-dashed border-border px-4 py-8 transition hover:border-primary hover:bg-primary/5"
          >
            <div class="text-center">
              <Upload v-if="!logoPreview" class="mx-auto mb-2 h-8 w-8 text-muted-foreground" />
              <CheckCircle v-else class="mx-auto mb-2 h-8 w-8 text-primary" />
              <p class="font-semibold text-foreground">{{ logoPreview ? 'Logo carregada com sucesso' : 'Clique para selecionar a logo' }}</p>
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
