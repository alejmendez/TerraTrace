<script setup>
import { useAttrs } from 'vue';

import DatePicker from 'primevue/datepicker';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';

const model = defineModel();
const attrs = useAttrs();

const props = defineProps({});

const emit = defineEmits(['change', 'input', 'focus', 'blur', 'keydown']);
</script>

<template>
  <template v-if="props.prefix || props.sufix">
    <InputGroup>
      <InputGroupAddon v-if="props.prefix">{{ props.prefix }}</InputGroupAddon>
      <DatePicker
        class="h-10 mt-1"
        v-bind="attrs"
        v-model="model"
        @update:modelValue="emit('change', $event)"
        @input="emit('input', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
        @keydown="emit('keydown', $event)"
      />
      <InputGroupAddon v-if="props.sufix">{{ props.sufix }}</InputGroupAddon>
    </InputGroup>
  </template>
  <template v-else>
    <DatePicker
      class="h-10 mt-1"
      v-bind="attrs"
      v-model="model"
      @update:modelValue="emit('change', $event)"
      @input="emit('input', $event)"
      @focus="emit('focus', $event)"
      @blur="emit('blur', $event)"
      @keydown="emit('keydown', $event)"
    />
  </template>
</template>
