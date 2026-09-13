<script setup>
import { Link } from '@inertiajs/vue3';

import CollectionIcon from './CollectionIcon.vue';

defineProps({
  actionLabel: {
    type: String,
    default: '',
  },
  actionRoute: {
    type: String,
    default: '',
  },
  description: {
    type: String,
    default: '',
  },
  title: {
    type: String,
    required: true,
  },
});
</script>

<template>
  <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
    <div>
      <p class="mb-2 text-sm font-medium text-[#60716c]">Gestión de campo</p>
      <h1 class="text-3xl font-extrabold tracking-[-0.035em] text-[#102f27] sm:text-4xl">{{ title }}</h1>
      <p v-if="description" class="mt-2 max-w-2xl text-base text-[#61716c]">{{ description }}</p>
    </div>

    <Link
      v-if="actionRoute && actionLabel"
      :href="actionRoute"
      class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-[#17663a] px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-[#11522e] focus:outline-none focus:ring-2 focus:ring-[#17663a] focus:ring-offset-2"
    >
      <CollectionIcon name="add" :size="20" aria-hidden="true" />
      {{ actionLabel }}
    </Link>
  </header>
</template>
