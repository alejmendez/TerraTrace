<script setup>
import { ref } from 'vue';

import CardSection from '@Core/Components/CardSection.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';
import VInput from '@Core/Components/Form/VInput.vue';

import AddPlantType from '@Core/Components/Form/AddPlantType.vue';
import { getDataSelect } from '@Core/Services/Selects';

const props = defineProps({
  form: Object,
  fields: Array,
  quarters: Array,
  types: Array,
  submitHandler: Function,
});

const form = props.form;
const plant_types = ref(props.types);

const quartersOptions = ref(props.quarters);

const addPlantTypeCallback = (newType) => {
  plant_types.value = [...plant_types.value, { value: newType.id, text: newType.name }];
};

const handlerChangeFieldId = async () => {
  quartersOptions.value = await getDataSelect('quarter', { field_id: form.field_id.value });
  form.quarter_id = null;
};

const handler_input_row = (e) => {
  const filteredValue = e.target.value.replace(/[^a-zA-Z]/g, '').toUpperCase();
  e.target.value = filteredValue;
  form.row = filteredValue;
};
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CardSection :header-text="__('plant.sections.location')">
      <VSelect
        id="field_id"
        v-model="form.field_id"
        :placeholder="__('generics.please_select')"
        :options="props.fields"
        :label="__('plant.form.field_id.label')"
        :message="form.errors.field_id"
        @change="handlerChangeFieldId"
      />

      <VSelect
        id="quarter_id"
        v-model="form.quarter_id"
        :placeholder="__('generics.please_select')"
        :options="quartersOptions"
        :label="__('plant.form.quarter_id.label')"
        :message="form.errors.quarter_id"
      />

      <VInput
        id="row"
        maxlength="2"
        v-model="form.row"
        @input="handler_input_row"
        :label="__('plant.form.row.label')"
        :message="form.errors.row"
      />
    </CardSection>

    <CardSection>
      <VInput
        id="code"
        v-model="form.code"
        :label="__('plant.form.code.label')"
        :message="form.errors.code"
      />

      <div class="grid grid-cols-12">
        <VSelect
          id="plant_type_id"
          v-model="form.plant_type_id"
          :classWrapper="'col-span-11'"
          :placeholder="__('generics.please_select')"
          :options="plant_types"
          :label="__('plant.form.plant_type_id.label')"
          :message="form.errors.plant_type_id"
        />
        <div class="ms-2" style="margin-top: 28px;">
          <AddPlantType :callback="addPlantTypeCallback" />
        </div>
      </div>

      <VInput
        id="planned_at"
        type="date"
        v-model="form.planned_at"
        :label="__('plant.form.planned_at.label')"
        :message="form.errors.planned_at"
        :max-date="new Date()"
      />

      <VInput
        id="nursery_origin"
        v-model="form.nursery_origin"
        :label="__('plant.form.nursery_origin.label')"
        :message="form.errors.nursery_origin"
      />
    </CardSection>
  </form>
</template>
