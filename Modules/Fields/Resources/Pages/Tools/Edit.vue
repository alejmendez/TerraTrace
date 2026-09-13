<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormTool from '@Fields/Pages/Tools/Form.vue';

import { stringToDate } from '@Core/Utils/date';

const props = defineProps({
  data: Object,
});

const { data } = props.data;

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  name: data.name,
  purchase_date: stringToDate(data.purchase_date),
  last_maintenance: stringToDate(data.last_maintenance),
  purchase_location: data.purchase_location,
  type: data.type,
  contact: data.contact,
  note: data.note,
});

const submitHandler = () => form.post(route('tools.update', data.id));
</script>

<template>
  <AuthenticatedLayout :title="__('tool.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('tool.titles.edit')"
      :breadcrumbs="[{ to: 'tools.index', text: __('tool.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('tools.index') }"
    />
    <FormTool
      :form="form"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
