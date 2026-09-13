<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormUser from '@Users/Components/Form.vue';

const props = defineProps({
  data: Object,
  roles: Array,
});

const { data } = props.data;

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  dni: data.dni,
  name: data.name,
  last_name: data.last_name,
  email: data.email,
  phone: data.phone,
  password: data.password,
  role: props.roles.find((a) => a.value == data.role.name),
  avatar: data.avatar,
  avatarRemove: false,
});

const submitHandler = () => form.post(route('users.update', data.id), form.avatar ? { forceFormData: true } : {});
</script>

<template>
  <AuthenticatedLayout :title="__('user.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('user.titles.edit')"
      :breadcrumbs="[{ to: 'users.index', text: __('user.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('users.index') }"
    />
    <FormUser
      :form="form"
      :roles="props.roles"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
