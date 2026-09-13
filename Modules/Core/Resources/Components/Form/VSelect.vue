<script setup>
import { useAttrs, ref, onMounted, watch, computed } from 'vue';

import Select from 'primevue/select';
import VElementFormWrapper from '@Core/Components/Form/VElementFormWrapper.vue';

const model = defineModel();

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
  options: {
    type: Array,
    default: [],
  },
  message: {
    type: String,
  },
  autofocus: {
    type: Boolean,
    default: false,
  },
});

watch(
  () => props.options,
  (newValue, _) => {
    if (!newValue.find((v) => v.value === model.value?.value)) {
      model.value = '';
    }
  },
);

const input = ref(null);
const isInvalid = computed(() => props.message !== '' && props.message !== undefined);

onMounted(() => {
  if (props.autofocus) {
    input.value.focus();
  }
});
</script>

<template>
  <VElementFormWrapper :classWrapper="props.classWrapper" :label="props.label" :message="props.message">
    <Select
      v-bind="attrs"
      v-model="model"
      fluid
      ref="input"
      optionLabel="text"
      :options="options"
      :invalid="isInvalid"
    />
  </VElementFormWrapper>
</template>
