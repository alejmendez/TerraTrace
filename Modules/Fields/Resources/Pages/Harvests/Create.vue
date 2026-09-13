<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormHarvest from '@Fields/Pages/Harvests/Form.vue';

const props = defineProps({
  quarters: Array,
  dogs: Array,
  users: Array,
  plant_codes: Array,
});

const form = useForm({
  date: null,
  batch: null,
  quarter_ids: [],
  dog_id: '',
  farmer_id: '',
  assistant_id: '',
  weight: 0,
  note: null,
});

const submitHandler = () => {
  form.post(route('harvests.store'));
};
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('harvest.titles.create')"
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('harvests.index') }"
    />
    <FormHarvest
      :form="form"
      :quarters="props.quarters"
      :dogs="props.dogs"
      :users="props.users"
      :plant_codes="props.plant_codes"
      :details="false"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
