<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import type { FunctionalComponent } from 'vue'
import type { LucideProps } from 'lucide-vue-next'

const props = defineProps<{
    label: string
    icon: FunctionalComponent<LucideProps>
    href: string
    method?: 'get' | 'post'
    danger?: boolean
}>()

const page = usePage()

const isActive = () => page.url.startsWith(props.href)
</script>

<template>
    <Link
        :href="href"
        :method="method ?? 'get'"
        as="button"
        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm transition"
        :class="[
            isActive() && !danger && 'bg-emerald-500 text-white',
            danger && 'text-red-400 hover:bg-red-500/10',
            !isActive() && !danger && 'text-white/80 hover:bg-white/10'
        ]"
    >
        <component :is="icon" class="h-5 w-5" />
        <span>{{ label }}</span>
    </Link>
</template>
