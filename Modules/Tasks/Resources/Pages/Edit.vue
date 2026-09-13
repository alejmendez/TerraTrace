<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormTask from '@Tasks/Components/Form.vue';

import { stringToDate } from '@Core/Utils/date';

const props = defineProps({
  data: Object,
  fields: Array,
  responsibles: Array,
  quarters: Array,
  plants: Array,
  tools: Array,
  security_equipments: Array,
  machineries: Array,
  task_priorities: Array,
  task_states: Array,
  task_repeat_type: Array,
  task_supplies_units: Array,
});

const { data } = props.data;

let supplies = data.supplies.map((a) => {
  a.unit = props.task_supplies_units.find((b) => b.value === a.unit);
  return a;
});

if (supplies.length === 0) {
  supplies = [
    {
      name: null,
      brand: null,
      quantity: null,
      unit: '',
    },
  ];
}

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  name: data.name,
  repeat_number: data.repeat_number,
  repeat_type: props.task_repeat_type.find((a) => a.value === data.repeat_type),
  status: props.task_states.find((a) => a.value === data.status),
  priority: props.task_priorities.find((a) => a.value === data.priority),
  start_date: stringToDate(data.start_date),
  end_date: stringToDate(data.end_date),
  field_id: data.field,
  quarter_id: props.quarters.filter((a) => data.quarters.includes(a.value)),
  rows: data.rows.map((a) => ({ value: a, text: a })),
  plant_id: props.plants.filter((a) => data.plants.includes(a.value)),
  responsible_id: props.responsibles.find((a) => a.value == data.responsible_id),
  note: data.note,
  comment: data.comments[0]?.comment || null,
  comments: data.comments,
  tools: data.tools,
  security_equipments: data.security_equipments,
  machineries: data.machineries,
  supplies,
});

const submitHandler = () => form.post(route('tasks.update', data.id));
</script>

<template>
  <AuthenticatedLayout :title="__('task.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('task.titles.edit')"
      :breadcrumbs="[{ to: 'tasks.index', text: __('task.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('tasks.index') }"
    />
    <FormTask
      :form="form"
      :fields="props.fields"
      :quarters="props.quarters"
      :plants="props.plants"
      :responsibles="props.responsibles"
      :tools="props.tools"
      :security_equipments="props.security_equipments"
      :machineries="props.machineries"
      :submitHandler="submitHandler"
      :task_priorities="props.task_priorities"
      :task_states="props.task_states"
      :task_repeat_type="props.task_repeat_type"
      :task_supplies_units="props.task_supplies_units"
    />
  </AuthenticatedLayout>
</template>
