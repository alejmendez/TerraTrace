<script setup>
import { useAttrs } from 'vue';

import InputText from 'primevue/inputtext';

import VInputDate from '@Core/Components/Form/VInputDate.vue';
import VTextarea from '@Core/Components/Form/VTextarea.vue';
import VInputNumber from '@Core/Components/Form/VInputNumber.vue';

import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';

import VElementFormWrapper from '@Core/Components/Form/VElementFormWrapper.vue';

const model = defineModel();

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
  message: {
    type: String,
  },
  prefix: {
    type: String,
    default: '',
  },
  sufix: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['change', 'input', 'click', 'focus', 'blur', 'keydown']);

const isInvalid = props.message !== '' && props.message !== undefined;
</script>

<template>
  <VElementFormWrapper :classWrapper="props.classWrapper" :label="props.label" :message="props.message">
    <template v-if="props.type === 'date'">
      <VInputDate
        v-bind="attrs"
        v-model="model"
        fluid
        :invalid="isInvalid"
        :showIcon="true"
        @change="emit('change', $event)"
        @input="emit('input', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
        @keydown="emit('keydown', $event)"
      />
    </template>
    <template v-else-if="props.type === 'textarea'">
      <VTextarea
        v-bind="attrs"
        v-model="model"
        fluid
        rows="5"
        :invalid="isInvalid"
        @change="emit('change', $event)"
        @input="emit('input', $event)"
        @click="emit('click', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
        @keydown="emit('keydown', $event)"
      />
    </template>
    <template v-else-if="props.type === 'number'">
      <VInputNumber
        v-bind="attrs"
        v-model="model"
        fluid
        showButtons
        :invalid="isInvalid"
        @change="emit('change', $event)"
        @input="emit('input', $event)"
        @click="emit('click', $event)"
        @focus="emit('focus', $event)"
        @blur="emit('blur', $event)"
        @keydown="emit('keydown', $event)"
      />
    </template>
    <template v-else>
      <template v-if="props.prefix || props.sufix">
        <InputGroup>
          <InputGroupAddon v-if="props.prefix">{{ props.prefix }}</InputGroupAddon>
          <InputText
            v-bind="attrs"
            v-model="model"
            fluid
            :invalid="isInvalid"
            @change="emit('change', $event)"
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
        <InputText
          v-bind="attrs"
          v-model="model"
          fluid
          :type="props.type"
          :invalid="isInvalid"
          @update:modelValue="emit('change', $event)"
          @input="emit('input', $event)"
          @click="emit('click', $event)"
          @focus="emit('focus', $event)"
          @blur="emit('blur', $event)"
          @keydown="emit('keydown', $event)"
        />
      </template>
    </template>
  </VElementFormWrapper>
</template>
