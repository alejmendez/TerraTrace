<script setup>
import { ref } from 'vue';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionFileInput from '@Core/Components/Collection/CollectionFileInput.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';

const props = defineProps({
  form: Object,
  fields: Array,
  responsibles: Array,
  submitHandler: Function,
});

const form = props.form;

const blueprintPreview = ref(form.blueprint);

const changeFileHandler = (e) => {
  form.blueprint = e.fileInput;
  form.blueprintRemove = e.fileRemove;
};
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CollectionCardSection>
      <CollectionSelect
        id="field_id"
        v-model="form.field_id"
        :placeholder="__('generics.please_select')"
        :options="props.fields"
        :label="__('quarter.form.field_id.label')"
        :message="form.errors.field_id"
      />
    </CollectionCardSection>

    <CollectionCardSection>
      <CollectionInput
        id="name"
        v-model="form.name"
        :label="__('quarter.form.name.label')"
        :message="form.errors.name"
      />

      <CollectionInput
        id="area"
        type="number"
        v-model="form.area"
        :min="0"
        :max="999"
        :step="0.01"
        :label="__('quarter.form.area.label')"
        :message="form.errors.area"
      />

      <CollectionSelect
        id="responsible_id"
        v-model="form.responsible_id"
        :placeholder="__('generics.please_select')"
        :options="props.responsibles"
        :label="__('quarter.form.responsible_id.label')"
        :message="form.errors.responsible_id"
      />
    </CollectionCardSection>

    <CollectionCardSection :header-text="__('quarter.sections.blueprint')">
      <div class="form-text col-span-2 form-text-type">
        <CollectionFileInput
          :image="blueprintPreview"
          :imagePreview="true"
          :label="__('quarter.form.blueprint.label')"
          @change="changeFileHandler"
        />
      </div>
    </CollectionCardSection>
  </form>
</template>
