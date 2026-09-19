<script setup>
import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  qualities: {
    type: Array,
    required: true,
  },
  hasError: {
    type: Boolean,
    required: true,
  },
});

const emit = defineEmits(['submit', 'submitAndLoadAnother', 'resetQr']);
</script>

<template>
  <div>
    <CollectionSelect
      id="quality"
      v-model="form.quality"
      :placeholder="__('generics.please_select')"
      :options="qualities"
      :label="__('harvest.form.details.quality.label')"
      :message="form.errors.quality"
    />

    <CollectionInput
      id="weight"
      v-model="form.weight"
      type="number"
      :min="0"
      :max="2000"
      :step="0.01"
      :label="__('harvest.form.details.weight.label')"
      :message="form.errors.weight"
    />

    <div class="mt-20 mb-20">
      <CollectionButton
        class="w-full text-xl h-16"
        severity="secondary"
        :loading="form.processing"
        :disabled="hasError"
        @click="$emit('submitAndLoadAnother')"
        label="Guardar y cargar otra"
      />

      <CollectionButton
        class="w-full mt-3 text-xl h-16"
        :loading="form.processing"
        :disabled="hasError"
        @click="$emit('submit')"
        label="Guardar"
      />

      <CollectionButton
        class="w-full mt-3 text-xl h-16"
        :loading="form.processing"
        :disabled="hasError"
        severity="danger"
        @click="$emit('resetQr')"
        label="Cancelar"
      />
    </div>
  </div>
</template>
