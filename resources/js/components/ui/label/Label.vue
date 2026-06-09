<script setup lang="ts">
import { cn } from '@/lib/utils'
import { Label, type LabelProps } from 'reka-ui'
import { computed, type HTMLAttributes } from 'vue'

interface Props extends /* @vue-ignore */ LabelProps {
  class?: HTMLAttributes['class']
  required?: boolean
}

const props = defineProps<Props>()

const delegatedProps = computed(() => {
  const { class: _, required: __, ...delegated } = props

  return delegated
})
</script>

<template>
  <Label
    data-slot="label"
    v-bind="delegatedProps"
    :class="
      cn(
        'mb-1.5 block text-sm font-medium leading-none select-none group-data-[disabled=true]:pointer-events-none group-data-[disabled=true]:opacity-50 peer-disabled:cursor-not-allowed peer-disabled:opacity-50',
        props.class,
      )
    "
  >
    <slot />
    <span v-if="required" class="ml-0.5 text-destructive">*</span>
  </Label>
</template>
