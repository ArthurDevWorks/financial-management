import { ref } from 'vue'

export type ThemeColor = 'teal' | 'emerald' | 'blue' | 'violet' | 'amber'

const STORAGE_KEY = 'theme-color'
const themes: ThemeColor[] = ['teal', 'emerald', 'blue', 'violet', 'amber']

const currentTheme = ref<ThemeColor>('blue')

function applyThemeColor(color: ThemeColor) {
    document.documentElement.setAttribute('data-theme', color)
}

export function useTheme() {
    function updateThemeColor(color: ThemeColor) {
        currentTheme.value = color
        localStorage.setItem(STORAGE_KEY, color)
        document.cookie = `${STORAGE_KEY}=${color};path=/;max-age=31536000`
        applyThemeColor(color)
    }

    function initializeThemeColor() {
        const stored = localStorage.getItem(STORAGE_KEY) as ThemeColor | null
        const cookie = document.cookie
            .split('; ')
            .find(row => row.startsWith(STORAGE_KEY))
            ?.split('=')[1] as ThemeColor | undefined

        const color = stored || cookie || 'blue'
        if (themes.includes(color)) {
            currentTheme.value = color
            applyThemeColor(color)
        }
    }

    return {
        currentTheme,
        themes,
        updateThemeColor,
        initializeThemeColor,
    }
}
