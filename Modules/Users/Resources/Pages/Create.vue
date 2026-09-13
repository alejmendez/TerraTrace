<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormUser from '@Users/Components/Form.vue';

const props = defineProps({
  roles: Array,
});

const form = useForm({
  dni: null,
  name: null,
  last_name: null,
  email: null,
  phone: null,
  password: null,
  role: '',
  avatar: null,
});

const submitHandler = () => form.post(route('users.store'), form.avatar ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('user.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('user.titles.create')"
      :breadcrumbs="[{ to: 'users.index', text: __('user.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('users.index') }"
    />
    <FormUser
      :form="form"
      :roles="props.roles"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
