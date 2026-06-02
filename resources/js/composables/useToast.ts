import { toast as sonnerToast } from 'vue-sonner';
import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

type ToastType = 'success' | 'error' | 'info' | 'warning';

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

  return { show };
}

export function useFlashMessages() {
  const page = usePage<{ flash?: { success?: string; error?: string; info?: string; warning?: string } }>();

  watch(
    () => page.props.flash,
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
