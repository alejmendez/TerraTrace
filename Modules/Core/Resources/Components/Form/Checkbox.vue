<script setup>
import { useAttrs } from 'vue';

import Checkbox from 'primevue/checkbox';
const attrs = useAttrs();
const model = defineModel();
</script>

<template>
  <Checkbox v-bind="attrs" v-model="model" :binary="true" />
</template>
