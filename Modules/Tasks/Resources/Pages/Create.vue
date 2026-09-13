<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';

import FormTask from '@Tasks/Components/Form.vue';

const props = defineProps({
  fields: Array,
  responsibles: Array,
  tools: Array,
  security_equipments: Array,
  machineries: Array,
  task_priorities: Array,
  task_states: Array,
  task_repeat_type: Array,
  task_supplies_units: Array,
});

const form = useForm({
  id: null,
  name: null,
  repeat_number: '1',
  repeat_type: props.task_repeat_type.find((a) => a.value === 'daily'),
  status: props.task_states.find((a) => a.value === 'to_begin'),
  priority: props.task_priorities.find((a) => a.value === 'when_possible'),
  start_date: null,
  end_date: null,
  field_id: [],
  quarter_id: [],
  plant_id: [],
  rows: [],
  responsible_id: [],
  note: null,
  comment: null,
  comments: [
    {
      id: null,
      comment: null,
      user_id: null,
      user_name: null,
      user_avatar: null,
      created_at: null,
    },
  ],
  tools: [],
  security_equipments: [],
  machineries: [],
  supplies: [
    {
      name: null,
      brand: null,
      quantity: null,
      unit: '',
    },
  ],
});

const submitHandler = () => form.post(route('tasks.store'));
</script>

<template>
  <AuthenticatedLayout :title="__('task.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('task.titles.create')"
      :breadcrumbs="[{ to: 'tasks.index', text: __('task.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('tasks.index') }"
    />
    <FormTask
      :form="form"
      :fields="props.fields"
      :responsibles="props.responsibles"
      :tools="props.tools"
      :security_equipments="props.security_equipments"
      :machineries="props.machineries"
      :submitHandler="submitHandler"
      :task_priorities="props.task_priorities"
      :task_states="props.task_states"
      :task_supplies_units="props.task_supplies_units"
    />
  </AuthenticatedLayout>
</template>
