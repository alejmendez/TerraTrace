<script setup>
import { computed } from 'vue';

import CollectionIcon from './CollectionIcon.vue';

const props = defineProps({
  meta: {
    type: Object,
    required: true,
  },
  perPageOptions: {
    type: Array,
    default: () => [12, 24, 50],
  },
});

defineEmits(['page', 'per-page']);

const pages = computed(() => {
  const currentPage = props.meta.current_page || 1;
  const lastPage = props.meta.last_page || 1;
  const firstPage = Math.max(1, currentPage - 2);
  const finalPage = Math.min(lastPage, firstPage + 4);

  return Array.from({ length: finalPage - firstPage + 1 }, (_, index) => firstPage + index);
});
</script>

<template>
  <footer class="flex flex-col gap-3 border-t border-[#e9efea] px-4 py-4 text-sm text-[#61716c] sm:flex-row sm:items-center sm:justify-between">
    <p>Mostrando {{ meta.from || 0 }} a {{ meta.to || 0 }} de {{ meta.total || 0 }} registros</p>

    <div class="flex flex-wrap items-center gap-2">
      <label class="sr-only" for="collection-per-page">Registros por página</label>
      <select
        id="collection-per-page"
        class="h-9 rounded-md border border-[#d7e0d9] bg-white px-2 text-sm text-[#284238] focus:border-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
        :value="meta.per_page"
        @change="$emit('per-page', $event.target.value)"
      >
        <option v-for="option in perPageOptions" :key="option" :value="option">{{ option }} por página</option>
      </select>

      <button class="collection-page-button" type="button" aria-label="Página anterior" :disabled="meta.current_page <= 1" @click="$emit('page', meta.current_page - 1)">
        <CollectionIcon name="chevron_left" :size="18" aria-hidden="true" />
      </button>
      <button
        v-for="page in pages"
        :key="page"
        class="collection-page-button"
        :class="{ 'collection-page-button--active': page === meta.current_page }"
        type="button"
        :aria-label="`Página ${page}`"
        :aria-current="page === meta.current_page ? 'page' : undefined"
        @click="$emit('page', page)"
      >
        {{ page }}
      </button>
      <button class="collection-page-button" type="button" aria-label="Página siguiente" :disabled="meta.current_page >= meta.last_page" @click="$emit('page', meta.current_page + 1)">
        <CollectionIcon name="chevron_right" :size="18" aria-hidden="true" />
      </button>
    </div>
  </footer>
</template>

<style scoped>
.collection-page-button { display: inline-flex; height: 2.25rem; min-width: 2.25rem; align-items: center; justify-content: center; border: 1px solid #d7e0d9; border-radius: 0.375rem; background: white; color: #284238; transition: background-color 150ms ease, border-color 150ms ease, color 150ms ease; }
.collection-page-button:hover:not(:disabled) { border-color: #17663a; color: #17663a; }
.collection-page-button:disabled { cursor: not-allowed; opacity: 0.45; }
.collection-page-button--active { border-color: #17663a; background: #17663a; color: white; }
</style>
