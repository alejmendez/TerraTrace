<script setup>
import { ref } from 'vue';

import CardSection from '@Core/Components/CardSection.vue';
import VInput from '@Core/Components/Form/VInput.vue';
import VInputDni from '@Core/Components/Form/VInputDni.vue';
import VInputFile from '@Core/Components/Form/VInputFile.vue';

const props = defineProps({
  form: Object,
  submitHandler: Function,
});

const form = props.form;
const documents = [...form.documents];

const blueprintPreview = ref(form.blueprint);

const changeFileHandler = (e) => {
  form.blueprint = e.fileInput;
  form.blueprintRemove = e.fileRemove;
};

const changeDocumentsHandler = (e) => {
  form.documents = e.fileInput;
  form.documentsRemove = e.fileRemove;
};
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CardSection>
      <VInput
        id="name"
        v-model="form.name"
        :label="__('field.form.name.label')"
        :message="form.errors.name"
      />

      <VInput
        id="location"
        maxlenmaxlength="100"
        v-model="form.location"
        :label="__('field.form.location.label')"
        :message="form.errors.location"
      />

      <VInput
        id="size"
        type="number"
        :min="0"
        :max="999"
        :step="0.01"
        v-model="form.size"
        :label="__('field.form.size.label')"
        :message="form.errors.size"
      />

      <VInput
        id="plants_count"
        :label="__('quarter.form.plants_count.label')"
        v-model="form.plants_count"
        readonly
        v-if="form.plants_count"
      />
    </CardSection>

    <CardSection>
      <VInputDni
        id="owner_dni"
        v-model="form.owner_dni"
        :label="__('field.form.owner_dni.label')"
        :message="form.errors.owner_dni"
      />

      <VInput
        id="owner_name"
        v-model="form.owner_name"
        :label="__('field.form.owner_name.label')"
        :message="form.errors.owner_name"
      />
    </CardSection>

    <CardSection :header-text="__('field.sections.blueprint')">
      <div class="form-text col-span-2 form-text-type">
        <VInputFile
          :image="blueprintPreview"
          :imagePreview="true"
          :label="__('field.form.blueprint.label')"
          @change="changeFileHandler"
        />
      </div>
    </CardSection>

    <CardSection :header-text="__('field.sections.documents')">
      <div class="form-text col-span-2 form-text-type">
        <VInputFile
          :files="documents"
          :imagePreview="false"
          :multiple="true"
          :label="__('field.form.documents.label')"
          @change="changeDocumentsHandler"
        />
      </div>
    </CardSection>
  </form>
</template>
