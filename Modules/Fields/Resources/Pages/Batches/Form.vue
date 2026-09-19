<script setup>
import { ref } from 'vue';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionMultiSelect from '@Core/Components/Collection/CollectionMultiSelect.vue';
import CollectionFieldWrapper from '@Core/Components/Collection/CollectionFieldWrapper.vue';
import AddImporter from '@Core/Components/Form/AddImporter.vue';

const props = defineProps({
  form: Object,
  importers: Array,
  harvests: Array,
  submitHandler: Function,
});

const form = props.form;
const importers = ref(props.importers);

const harvests = ref(props.harvests);

const addImporterCallback = (newType) => {
  importers.value = [...importers.value, { value: newType.id, text: newType.name }];
};
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CollectionCardSection>
      <CollectionInput
        id="batch_number"
        v-model="form.batch_number"
        :label="__('batch.form.batch_number.label')"
        :message="form.errors.batch_number"
      />

      <CollectionInput
        id="delivery_date"
        type="date"
        v-model="form.delivery_date"
        :label="__('batch.form.delivery_date.label')"
        :message="form.errors.delivery_date"
        :maxDate="new Date()"
      />

      <div class="grid grid-cols-12">
        <CollectionSelect
          id="importer_id"
          v-model="form.importer_id"
          classWrapper="col-span-11"
          :placeholder="__('generics.please_select')"
          :options="importers"
          :label="__('batch.form.importer_id.label')"
          :message="form.errors.importer_id"
        />
        <div class="ms-2" style="margin-top: 28px;">
          <AddImporter :callback="addImporterCallback" />
        </div>
      </div>

      <CollectionFieldWrapper :classWrapper="props.classWrapper" :label="__('batch.form.harvests.label')" :message="form.errors.harvests">
        <CollectionMultiSelect
          id="harvests"
          v-model="form.harvests"
          option-group-label="label"
          option-group-children="items"
          option-label="label"
          :placeholder="__('generics.please_select')"
          :options="harvests"
        />
      </CollectionFieldWrapper>

      <CollectionInput
        id="carrier"
        v-model="form.carrier"
        :label="__('batch.form.carrier.label')"
        :message="form.errors.carrier"
      />

      <CollectionInput
        id="current_weight"
        type="number"
        v-model="form.current_weight"
        :label="__('batch.form.current_weight.label')"
        :message="form.errors.current_weight"
      />
    </CollectionCardSection>
  </form>
</template>
