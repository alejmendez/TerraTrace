<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormDog from '@Fields/Pages/Dogs/Form.vue';

import { stringToDate, getAge } from '@Core/Utils/date';

const props = defineProps({
  data: Object,
  fields: Array,
  couples: Array,
  genders: Array,
});

const { data } = props.data;

const vaccines =
  data.vaccines.length > 0
    ? data.vaccines.map((v) => {
        v.date = stringToDate(v.date);
        return v;
      })
    : [
        {
          id: null,
          name: null,
          date: null,
        },
      ];

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  name: data.name,
  breed: data.breed,
  gender: data.gender,
  birthdate: stringToDate(data.birthdate),
  age: getAge(data.birthdate),
  veterinary: data.veterinary,
  couple_id: props.couples.find((a) => a.value == data.couple.id),
  avatar: data.avatar,
  avatarRemove: false,
  field_id: props.fields.find((a) => a.value == data.field.id),
  vaccines,
});

const submitHandler = () => form.post(route('dogs.update', data.id), form.avatar ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('dog.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('dog.titles.edit')"
      :breadcrumbs="[{ to: 'dogs.index', text: __('dog.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('dogs.index') }"
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
