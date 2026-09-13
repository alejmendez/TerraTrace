<script setup>
import { useAttrs, computed } from 'vue';

import Label from '@Core/Components/Form/Label.vue';

const attrs = useAttrs();

const props = defineProps({
  classWrapper: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  message: {
    type: String,
  },
});

const isInvalid = computed(() => props.message !== '' && props.message !== undefined);
</script>

<template>
  <div :class="props.classWrapper">
    <Label
      :class="{ 'text-red-600' : isInvalid }"
      :for="attrs.id"
      v-if="props.label !== ''"
    >
      {{ props.label }}
    </Label>

    <div class="h-1"></div>

    <slot />

    <div v-show="props.message">
      <p class="text-sm text-red-600">
        {{ props.message }}
      </p>
    </div>
  </div>
</template>
