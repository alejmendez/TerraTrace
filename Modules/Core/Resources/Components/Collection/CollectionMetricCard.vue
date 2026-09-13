<script setup>
import CollectionIcon from './CollectionIcon.vue';

defineProps({
  icon: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    required: true,
  },
  tone: {
    type: String,
    default: 'green',
  },
  value: {
    type: [Number, String],
    required: true,
  },
});
</script>

<template>
  <article class="flex min-w-0 items-center gap-3 rounded-xl border border-[#e1e9e3] bg-white p-4 shadow-[0_3px_14px_rgba(24,57,39,0.045)]">
    <span
      class="flex size-12 shrink-0 items-center justify-center rounded-full text-2xl"
      :class="tone === 'warning' ? 'bg-[#fff1d4] text-[#a96400]' : 'bg-[#e3f4e6] text-[#17663a]'"
      aria-hidden="true"
    >
      <CollectionIcon :name="icon" :size="24" />
    </span>
    <div class="min-w-0">
      <p class="truncate text-2xl font-bold tracking-[-0.03em] text-[#102f27]">{{ value }}</p>
      <p class="mt-0.5 text-sm text-[#61716c]">{{ label }}</p>
    </div>
  </article>
</template>
