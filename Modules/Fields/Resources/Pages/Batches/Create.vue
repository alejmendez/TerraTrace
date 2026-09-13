<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import FormBatch from '@Fields/Pages/Batches/Form.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';

const props = defineProps({
  importers: Array,
  harvests: Array,
});

const form = useForm({
  batch_number: null,
  delivery_date: null,
  importer_id: [],
  harvests: [],
  carrier: null,
  current_weight: null,
});

const submitHandler = () => form.post(route('batches.store'));
</script>

<template>
  <AuthenticatedLayout :title="__('batch.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('batch.titles.create')"
      :breadcrumbs="[{ to: 'batches.index', text: __('batch.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('batches.index') }"
    />
    <FormBatch
      :form="form"
      :importers="importers"
      :harvests="harvests"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
