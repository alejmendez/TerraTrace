<script setup>
import { useAttrs } from 'vue';

const attrs = useAttrs();
</script>

<template>
  <label class="input-label" v-bind="attrs">
    <slot />
  </label>
</template>
