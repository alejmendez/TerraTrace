<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormQuarter from '@Fields/Pages/Quarters/Form.vue';

const props = defineProps({
  fields: Array,
  responsibles: Array,
});

const form = useForm({
  name: null,
  location: null,
  area: null,
  plants_count: 0,
  field_id: null,
  responsible_id: null,
  blueprint: null,
});

const submitHandler = () => form.post(route('quarters.store'), form.blueprint ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('quarter.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('quarter.titles.create')"
      :breadcrumbs="[{ to: 'quarters.index', text: __('quarter.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('quarters.index') }"
    />
    <FormQuarter
      :form="form"
      :fields="props.fields"
      :responsibles="props.responsibles"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
