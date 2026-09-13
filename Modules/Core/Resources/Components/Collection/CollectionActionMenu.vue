<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import CollectionIcon from './CollectionIcon.vue';

defineProps({
  editRoute: { type: String, default: '' },
  showRoute: { type: String, default: '' },
  showDestroy: { type: Boolean, default: false },
});

defineEmits(['destroy']);

const open = ref(false);
const close = () => { open.value = false; };
</script>

<template>
  <div class="relative shrink-0">
    <button
      class="flex size-9 items-center justify-center rounded-md border border-[#dbe5dd] bg-white text-[#456056] transition hover:border-[#17663a] hover:text-[#17663a] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
      type="button"
      aria-label="Abrir acciones"
      :aria-expanded="open"
      @click="open = !open"
      @keydown.escape="close"
    >
      <CollectionIcon name="more_horiz" :size="20" aria-hidden="true" />
    </button>

    <div v-if="open" class="absolute right-0 z-20 mt-2 w-36 rounded-lg border border-[#dbe5dd] bg-white p-1 shadow-lg" role="menu">
      <Link v-if="showRoute" :href="showRoute" class="collection-action" role="menuitem" @click="close">Ver ficha</Link>
      <Link v-if="editRoute" :href="editRoute" class="collection-action" role="menuitem" @click="close">Editar</Link>
      <button v-if="showDestroy" class="collection-action collection-action--danger" type="button" role="menuitem" @click="$emit('destroy'); close()">Eliminar</button>
    </div>
  </div>
</template>

<style scoped>
.collection-action { display: block; width: 100%; padding: 0.55rem 0.65rem; border-radius: 0.375rem; color: #284238; font-size: 0.875rem; text-align: left; }
.collection-action:hover { background: #eef6ef; color: #17663a; }
.collection-action--danger:hover { background: #fff0f2; color: #be123c; }
</style>
