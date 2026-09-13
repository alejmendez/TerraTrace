<script setup>
import CollectionIcon from './CollectionIcon.vue';

defineProps({
  message: { type: String, default: '' },
  tone: { type: String, default: 'success' },
});

defineEmits(['dismiss']);
</script>

<template>
  <div v-if="message" class="fixed right-5 bottom-5 z-50 flex max-w-sm items-start gap-3 rounded-xl border bg-white p-4 shadow-xl" :class="tone === 'error' ? 'border-[#fecdd3]' : 'border-[#cce7d2]'" role="status">
    <CollectionIcon :name="tone === 'error' ? 'error' : 'check_circle'" :size="20" :class="tone === 'error' ? 'text-[#be123c]' : 'text-[#17663a]'" aria-hidden="true" />
    <p class="flex-1 text-sm text-[#284238]">{{ message }}</p>
    <button class="text-[#61716c] hover:text-[#102f27]" type="button" aria-label="Cerrar aviso" @click="$emit('dismiss')"><CollectionIcon name="close" :size="18" aria-hidden="true" /></button>
  </div>
</template>
