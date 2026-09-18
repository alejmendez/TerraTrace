<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import CollectionPageHeader from '@Core/Components/Collection/CollectionPageHeader.vue';
import FormTool from '@Fields/Pages/Tools/Form.vue';

const form = useForm({
  name: null,
  purchase_date: null,
  last_maintenance: null,
  purchase_location: null,
  type: null,
  contact: null,
  note: null,
});

const submitHandler = () => form.post(route('tools.store'));
</script>

<template>
  <AuthenticatedLayout :title="__('tool.titles.entity_breadcrumb')">
    <CollectionPageHeader
      :title="__('tool.titles.create')"
      :breadcrumbs="[{ to: 'tools.index', text: __('tool.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('tools.index') }"
    />
    <FormTool
      :form="form"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
