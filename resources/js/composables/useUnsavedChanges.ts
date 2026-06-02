import { onBeforeUnmount, onMounted, ref } from 'vue';

export function useUnsavedChanges(isDirty: () => boolean) {
  const showConfirm = ref(false);
  let pendingNavigation: (() => void) | null = null;

  const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (isDirty()) {
      event.preventDefault();
      event.returnValue = '';
    }
  };

  onMounted(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
  });

  onBeforeUnmount(() => {
    window.removeEventListener('beforeunload', handleBeforeUnload);
  });

  const onBeforeNavigate = () => {
    if (isDirty()) {
      return new Promise<void>((resolve) => {
        showConfirm.value = true;
        pendingNavigation = resolve;
      });
    }
  };

  function confirmNavigate() {
    showConfirm.value = false;
    pendingNavigation?.();
    pendingNavigation = null;
    window.removeEventListener('beforeunload', handleBeforeUnload);
  }

  function cancelNavigate() {
    showConfirm.value = false;
    pendingNavigation = null;
  }

  return {
    showConfirm,
    onBeforeNavigate,
    confirmNavigate,
    cancelNavigate,
  };
}
