<script setup>
import { useAttrs } from 'vue';
import Checkbox from 'primevue/checkbox';

import Label from '@Core/Components/Form/Label.vue';

const model = defineModel({ default: false });

const attrs = useAttrs();

const props = defineProps({
  classWrapper: {
    type: String,
    default: '',
  },
  type: {
    type: String,
    default: 'text',
  },
  label: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['click']);
</script>

<template>
  <div :class="props.classWrapper">
    <template v-if="props.label === ''">
      <Checkbox
        v-bind="attrs"
        :disabled="props.disabled"
        @click="emit('click', $event)"
        v-model="model"
        binary
      />
    </template>
    <template v-else>
      <Label>
        <Checkbox
          v-bind="attrs"
          :disabled="props.disabled"
          @click="emit('click', $event)"
          v-model="model"
          binary
        />
        {{ props.label }}
      </Label>
    </template>
  </div>
</template>
