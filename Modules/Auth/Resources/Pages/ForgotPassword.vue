<script setup>
import { useForm, Head } from '@inertiajs/vue3';

import GuestLayout from '@Core/Layouts/GuestLayout.vue';
import InputError from '@Core/Components/InputError.vue';
import InputLabel from '@Core/Components/InputLabel.vue';
import TextInput from '@Core/Components/TextInput.vue';
import PrimaryButton from '@Core/Components/PrimaryButton.vue';

defineProps({
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
});

const submit = () => {
  form.post(route('password.email'));
};
</script>

<template>
  <GuestLayout>
    <Head :title="__('auth.forgotPassword.title')" />

    <div class="mb-4 text-sm text-gray-600">
      {{ __('auth.forgotPassword.subtitle') }}
    </div>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <div>
        <InputLabel for="email" :value="__('auth.forgotPassword.form.email')" />

        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          autofocus
          autocomplete="username"
        />

        <InputError class="mt-2" :message="form.errors.email" />
      </div>

      <div class="flex items-center justify-end mt-4">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ __('auth.forgotPassword.form.submit') }}
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
