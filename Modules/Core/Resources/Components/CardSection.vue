<script setup>
const props = defineProps({
  headerText: String,
  wrapperClass: {
    type: String,
    default: 'p-6 grid md:grid-cols-2 gap-x-10 gap-y-5 sm:grid-cols-1',
  },
  sectionClass: {
    type: String,
    default: 'mt-5 rounded-xl border border-[#e1e9e3] bg-white shadow-[0_3px_14px_rgba(24,57,39,0.045)]',
  },
});
</script>

<template>
  <section class="card-section" :class="props.sectionClass">
    <slot name="header"></slot>
    <header class="flex items-center gap-x-3 overflow-hidden px-6 py-4" v-if="props.headerText">
      <h3 class="text-lg font-extrabold leading-6 text-[#12352a] dark:text-gray-100">
        {{ props.headerText }}
      </h3>
    </header>
    <div class="border-gray-200 dark:border-gray-800" :class="{ 'border-t': props.headerText }">
      <div :class="props.wrapperClass" >
        <slot />
      </div>
    </div>
  </section>
</template>
