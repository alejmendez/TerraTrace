<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormField from '@Fields/Pages/Fields/Form.vue';

const props = defineProps({
  data: Object,
});

const { data } = props.data;

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  name: data.name,
  location: data.location,
  size: data.size,
  plants_count: data.plants_count,
  owner_dni: data.owner.dni,
  owner_name: data.owner.name,
  documents: data.documents,
  documentsRemove: [],
  blueprint: data.blueprint,
  blueprintRemove: false,
});

const submitHandler = () => form.post(route('fields.update', data.id), form.blueprint || form.documents ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('field.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('field.titles.edit')"
      :breadcrumbs="[{ to: 'fields.index', text: __('field.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('fields.index') }"
    />
    <FormField
      :form="form"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
