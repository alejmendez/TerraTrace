<script setup>
import CollectionIcon from './CollectionIcon.vue';

defineProps({
  message: { type: String, default: '' },
  title: { type: String, default: 'Confirmar eliminación' },
  visible: { type: Boolean, default: false },
});

defineEmits(['cancel', 'confirm']);
</script>

<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-[#102f27]/35 p-4" role="presentation">
    <section class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl" role="alertdialog" aria-modal="true" :aria-label="title">
      <span class="flex size-11 items-center justify-center rounded-full bg-[#fff0f2] text-[#be123c]" aria-hidden="true"><CollectionIcon name="delete" :size="22" /></span>
      <h2 class="mt-4 text-xl font-bold text-[#102f27]">{{ title }}</h2>
      <p class="mt-2 text-sm leading-6 text-[#61716c]">{{ message }}</p>
      <div class="mt-6 flex justify-end gap-3">
        <button class="collection-dialog-button" type="button" @click="$emit('cancel')">Cancelar</button>
        <button class="collection-dialog-button collection-dialog-button--danger" type="button" @click="$emit('confirm')">Eliminar</button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.collection-dialog-button { min-height: 2.75rem; padding: 0.5rem 0.875rem; border: 1px solid #d7e0d9; border-radius: 0.5rem; color: #284238; font-weight: 600; }
.collection-dialog-button--danger { border-color: #be123c; background: #be123c; color: white; }
</style>
