<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormMachineries from '@Fields/Pages/Machineries/Form.vue';

const form = useForm({
  name: null,
  purchase_date: null,
  last_maintenance: null,
  purchase_location: null,
  type: null,
  contact: null,
  note: null,
});

const submitHandler = () => form.post(route('machineries.store'));
</script>

<template>
  <AuthenticatedLayout :title="__('machinery.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('machinery.titles.create')"
      :breadcrumbs="[{ to: 'machineries.index', text: __('machinery.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('machineries.index') }"
    />
    <FormMachineries
      :form="form"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
