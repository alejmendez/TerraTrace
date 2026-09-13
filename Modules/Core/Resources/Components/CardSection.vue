<script setup>
const props = defineProps({
  headerText: String,
  wrapperClass: {
    type: String,
    default: 'p-6 grid md:grid-cols-2 gap-x-16 gap-y-4 sm:grid-cols-1',
  },
  sectionClass: {
    type: String,
    default: 'mt-5 rounded-xl shadow-sm ring-1 ring-gray-950/5',
  },
});
</script>

<template>
  <section class="card-section" :class="props.sectionClass">
    <slot name="header"></slot>
    <header class="flex items-center gap-x-3 overflow-hidden px-6 py-4" v-if="props.headerText">
      <h3 class="text-xl font-bold leading-6 text-gray-900 dark:text-gray-100">
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
