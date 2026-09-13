<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormDog from '@Fields/Pages/Dogs/Form.vue';

const props = defineProps({
  fields: Array,
  fields: Array,
  couples: Array,
  genders: Array,
});

const form = useForm({
  name: null,
  breed: null,
  gender: '',
  birthdate: null,
  veterinary: null,
  couple_id: '',
  avatar: null,
  age: null,
  field_id: '',
  vaccines: [
    {
      name: null,
      date: null,
    },
  ],
});

const submitHandler = () => form.post(route('dogs.store'), form.avatar ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('dog.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('dog.titles.create')"
      :breadcrumbs="[{ to: 'dogs.index', text: __('dog.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('dogs.index') }"
    />
    <FormDog
      :form="form"
      :fields="props.fields"
      :couples="props.couples"
      :genders="props.genders"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
