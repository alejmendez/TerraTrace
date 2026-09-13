<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormQuarter from '@Fields/Pages/Quarters/Form.vue';

const props = defineProps({
  data: Object,
  fields: Array,
  responsibles: Array,
});

const { data } = props.data;

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  name: data.name,
  location: data.location,
  area: data.area,
  plants_count: data.plants_count,
  field_id: props.fields.find((a) => a.value == data.field.id),
  responsible_id: props.responsibles.find((a) => a.value == data.responsible.id),
  blueprint: data.blueprint,
  blueprintRemove: false,
});

const submitHandler = () => form.post(route('quarters.update', data.id), form.blueprint ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('quarter.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('quarter.titles.edit')"
      :breadcrumbs="[{ to: 'quarters.index', text: __('quarter.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('quarters.index') }"
    />
    <FormQuarter
      :form="form"
      :fields="props.fields"
      :responsibles="props.responsibles"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
