<script setup>
import { useAttrs, computed } from 'vue';

import MultiSelect from 'primevue/multiselect';
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
});

const emit = defineEmits(['change', 'blur']);
const isInvalid = computed(() => props.message !== '' && props.message !== undefined);
</script>

<template>
  <VElementFormWrapper :classWrapper="props.classWrapper" :label="props.label" :message="props.message">
    <MultiSelect
      v-model="model"
      v-bind="attrs"
      filter
      fluid
      optionLabel="text"
      :options="options"
      :maxSelectedLabels="3"
      :invalid="isInvalid"
    >
      <template #optiongroup="slotProps">
        <div class="flex items-center">
          <div>{{ slotProps.option.text }}</div>
        </div>
      </template>
    </MultiSelect>
  </VElementFormWrapper>
</template>
