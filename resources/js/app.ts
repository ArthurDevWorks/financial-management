import '../css/app.css';
import 'vue-sonner/style.css';

import { useFlashMessages, useToast } from './composables/useToast';
import { initializeTheme } from './composables/useAppearance';
import { useTheme } from './composables/useTheme';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { Toaster } from 'vue-sonner';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

router.on('error', (errors) => {
    if (Object.keys(errors).length === 0) return;

    useToast().error(
        'Não foi possível concluir a operação. Verifique os dados e tente novamente.',
    );
});

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        createApp({
            setup() {
                useFlashMessages();
                return () => [
                    h(App, props),
                    h(Toaster, {
                        richColors: true,
                        closeButton: true,
                        position: 'top-right',
                        toastOptions: { class: '!font-sans' },
                    }),
                ];
            },
        })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// This will set the theme color (teal/blue/green/purple) on page load...
const { initializeThemeColor } = useTheme();
initializeThemeColor();
