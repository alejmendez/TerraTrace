<script setup>
import { useAttrs, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import ButtonPrime from 'primevue/button';

const attrOriginal = useAttrs();
const attrs = computed(() => {
  const { href, onClick, ...rest } = attrOriginal;
  return rest;
});

const clickHandler = attrOriginal.onClick
  ? attrOriginal.onClick
  : () => {
      if (attrOriginal.href) {
        router.visit(attrOriginal.href);
      }
    };
</script>

<template>
  <ButtonPrime v-bind="attrs" @click="clickHandler" />
</template>
