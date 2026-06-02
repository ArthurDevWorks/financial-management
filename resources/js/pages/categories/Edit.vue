<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import FormPageLayout from '@/components/FormPageLayout.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import InputError from '@/components/InputError.vue'
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

interface Category {
  id: number
  type: string
  name: string
}

interface TypeOption {
  [key: string]: string
}

const props = defineProps<{
  category: Category
  types: TypeOption
}>()

const showUnsavedDialog = ref(false)

const form = useForm({
  type: props.category.type,
  name: props.category.name,
})

const submit = () => {
  form.put(`/categories/${props.category.id}`)
}

const goBack = () => {
  window.history.back()
}
</script>

<template>
  <AppLayout>
    <FormPageLayout
      title="Editar Categoria"
      description="Modifique os dados da categoria"
      :processing="form.processing"
      :dirty="form.isDirty"
      submit-label="Atualizar Categoria"
      processing-label="Atualizando..."
      v-model:showUnsavedDialog="showUnsavedDialog"
      @submit="submit"
      @cancel="goBack"
    >
      <div class="grid grid-cols-2 gap-6">
        <div>
          <Label>Tipo de Categoria</Label>
          <select
            v-model="form.type"
            required
            class="h-9 w-full rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
          >
            <option value="" disabled>Selecione um tipo</option>
            <option v-for="(label, value) in types" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
          <InputError :message="form.errors.type" />
        </div>

        <div>
          <Label>Nome da Categoria</Label>
          <Input v-model="form.name" type="text" placeholder="Digite o nome da categoria" />
          <InputError :message="form.errors.name" />
        </div>
      </div>
    </FormPageLayout>
  </AppLayout>
</template>
