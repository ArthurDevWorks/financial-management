import { usePage } from '@inertiajs/vue3';
import type { AppPageProps } from '@/types';
import { watch } from 'vue';
import { toast as sonnerToast } from 'vue-sonner';

export type ToastType = 'success' | 'error' | 'info' | 'warning';

export function useToast() {
    const show = (message: string, type: ToastType = 'info') => {
        switch (type) {
            case 'success':
                sonnerToast.success(message);
                break;
            case 'error':
                sonnerToast.error(message);
                break;
            case 'warning':
                sonnerToast.warning(message);
                break;
            default:
                sonnerToast.info(message);
        }
    };

    const success = (message: string) => sonnerToast.success(message);
    const error = (message: string) => sonnerToast.error(message);
    const info = (message: string) => sonnerToast.info(message);
    const warning = (message: string) => sonnerToast.warning(message);

    return { show, success, error, info, warning };
}

export function useFlashMessages() {
    const page = usePage<AppPageProps>();

    watch(
        () => page.props?.flash,
        (flash) => {
            if (!flash) return;
            if (flash.success) sonnerToast.success(flash.success);
            if (flash.error) sonnerToast.error(flash.error);
            if (flash.info) sonnerToast.info(flash.info);
            if (flash.warning) sonnerToast.warning(flash.warning);
        },
        { immediate: true, deep: true },
    );
}
