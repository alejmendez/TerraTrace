<script setup>
import { ref, watch } from 'vue';

import CardSection from '@Core/Components/CardSection.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';
import VSelectMultiple from '@Core/Components/Form/VSelectMultiple.vue';

import { getDataSelect } from '@Core/Services/Selects';

const props = defineProps({
  form: Object,
  fields: Array,
  quarters: Array,
  plants: Array,
  responsibles: Array,
});

const form = props.form;

const quarters = ref(props.quarters);
const plants = ref(props.plants);
const rows = generateLetterArray().map((r) => ({ value: r, text: r }));

function generateLetterArray() {
  const letters = [];

  for (let i = 0; i < 26; i++) {
    letters.push(String.fromCharCode(65 + i));
  }

  for (let i = 0; i < 1; i++) {
    for (let j = 0; j < 26; j++) {
      letters.push(String.fromCharCode(65 + i) + String.fromCharCode(65 + j));
    }
  }

  return letters;
}

watch(
  () => form.field_id,
  async (field_id) => {
    quarters.value = await getDataSelect('quarter', { field_id: field_id.value });
    form.quarter_id = null;
  },
);

watch(
  () => form.quarter_id,
  async (quarter_id) => {
    if (quarter_id === null) {
      plants.value = [];
      form.plant_id = null;
      return;
    }

    plants.value = await getDataSelect('plant', { quarter_id: quarter_id.map((a) => a.value), row: form.rows.map((a) => a.value) });
    form.plant_id = null;
  },
);

watch(
  () => form.rows,
  async (row) => {
    plants.value = await getDataSelect('plant', { quarter_id: form.quarter_id.map((a) => a.value), row: row.map((a) => a.value) });
    form.plant_id = null;
  },
);
</script>

<template>
  <CardSection :header-text="__('task.sections.assignment')">
    <VSelect
      id="field_id"
      v-model="form.field_id"
      :placeholder="__('generics.please_select')"
      :options="props.fields"
      :label="__('task.form.field_id.label')"
      :message="form.errors.field_id"
    />

    <VSelectMultiple
      id="quarter_id"
      v-model="form.quarter_id"
      :placeholder="__('generics.please_select')"
      :options="quarters"
      :label="__('task.form.quarter_id.label')"
      :message="form.errors.quarter_id"
    />

    <VSelectMultiple
      id="rows"
      v-model="form.rows"
      :placeholder="__('generics.please_select')"
      :options="rows"
      :virtualScrollerOptions="{ itemSize: 44 }"
      :label="__('task.form.rows.label')"
      :message="form.errors.rows"
    />

    <VSelectMultiple
      id="plant_id"
      v-model="form.plant_id"
      :placeholder="__('generics.please_select')"
      :options="plants"
      :virtualScrollerOptions="{ itemSize: 44 }"
      :label="__('task.form.plant_id.label')"
      :message="form.errors.plant_id"
    />

    <VSelect
      id="responsible_id"
      v-model="form.responsible_id"
      :placeholder="__('generics.please_select')"
      :options="props.responsibles"
      :label="__('task.form.responsible_id.label')"
      :message="form.errors.responsible_id"
    />
  </CardSection>
</template>
