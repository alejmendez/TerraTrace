<script setup>
import CardSection from '@Core/Components/CardSection.vue';
import VInput from '@Core/Components/Form/VInput.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';

const props = defineProps({
  form: Object,
  task_priorities: Array,
  task_states: Array,
});

const form = props.form;
</script>

<template>
  <CardSection>
    <VInput
      id="name"
      v-model="form.name"
      classWrapper="col-span-2"
      :label="__('task.form.name.label')"
      :message="form.errors.name"
    />

    <VSelect
      id="priority"
      v-model="form.priority"
      :placeholder="__('generics.please_select')"
      :options="props.task_priorities"
      :label="__('task.form.priority.label')"
      :message="form.errors.priority"
    />

    <VSelect
      id="status"
      v-model="form.status"
      :placeholder="__('generics.please_select')"
      :options="props.task_states"
      :label="__('task.form.status.label')"
      :message="form.errors.status"
    />

    <VInput
      id="start_date"
      type="date"
      v-model="form.start_date"
      :label="__('task.form.start_date.label')"
      :message="form.errors.start_date"
    />

    <VInput
      id="end_date"
      type="date"
      v-model="form.end_date"
      :label="__('task.form.end_date.label')"
      :message="form.errors.end_date"
    />
  </CardSection>
</template>
