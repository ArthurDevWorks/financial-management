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
        class="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm transition"
        :class="[
            isActive() && !danger && 'bg-cyan-500 text-slate-900 font-semibold',
            danger && 'text-red-400 hover:bg-red-500/10',
            !isActive() && !danger && 'text-slate-300 hover:bg-slate-700/50'
        ]"
    >
        <component :is="icon" class="h-5 w-5" />
        <span>{{ label }}</span>
    </Link>
</template>
