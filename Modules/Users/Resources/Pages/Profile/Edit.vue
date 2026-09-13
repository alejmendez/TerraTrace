<script setup>
import { onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormUser from '@Users/Components/Form.vue';

const toast = useToast();

const props = defineProps({
  data: Object,
  roles: Array,
  toast: Object,
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
  role: data.role.name,
  avatar: data.avatar,
  avatarRemove: false,
});

const submitHandler = () => form.post(route('profile.update'), form.avatar ? { forceFormData: true } : {});

onMounted(async () => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('profile.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('profile.titles.edit')"
      :breadcrumbs="[{ text: __('profile.titles.entity_breadcrumb') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('dashboard.index') }"
    />
    <FormUser
      :form="form"
      :roles="props.roles"
      :submitHandler="submitHandler"
      :showRole="false"
    />
  </AuthenticatedLayout>
</template>
