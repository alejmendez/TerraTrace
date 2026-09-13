<script setup>
import { useAttrs } from 'vue';

import Textarea from 'primevue/textarea';
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
      <Textarea
        ref="input"
        v-bind="attrs"
        v-model="model"
        fluid
        rows="5"
        @update:modelValue="emit('change', $event)"
        @input="emit('input', $event)"
        @click="emit('click', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
        @keydown="emit('keydown', $event)"
      />
      <InputGroupAddon v-if="props.sufix">{{ props.sufix }}</InputGroupAddon>
    </InputGroup>
  </template>
  <template v-else>
    <Textarea
      ref="input"
      v-bind="attrs"
      v-model="model"
      fluid
      rows="5"
      @update:modelValue="emit('change', $event)"
      @input="emit('input', $event)"
      @click="emit('click', $event)"
      @focus="emit('focus', $event)"
      @blur="emit('blur', $event)"
      @keydown="emit('keydown', $event)"
    />
  </template>
</template>
