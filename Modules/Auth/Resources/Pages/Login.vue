<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3';

import GuestLayout from '@Core/Layouts/GuestLayout.vue';
import Checkbox from '@Core/Components/Form/Checkbox.vue';
import VInput from '@Core/Components/Form/VInput.vue';
import Button from '@Core/Components/Form/Button.vue';

defineProps({
  canResetPassword: {
    type: Boolean,
  },
  status: {
    type: String,
  },
});

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>

<template>
  <GuestLayout>
    <Head :title="__('auth.login.title')" />

    <div class="my-4 text-4xl text-gray-900 dark:text-slate-300 font-bold text-center">
      {{ __('auth.login.title') }}
    </div>

    <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit">
      <VInput
        id="email"
        type="email"
        classWrapper="mt-4"
        v-model="form.email"
        :label="__('auth.login.form.email')"
        :message="form.errors.email"
      />

      <VInput
        id="password"
        type="password"
        autocomplete="current-password"
        classWrapper="mt-4"
        v-model="form.password"
        :label="__('auth.login.form.password')"
        :message="form.errors.password"
      />

      <div class="block mt-4">
        <label class="flex items-center">
          <Checkbox name="remember" v-model="form.remember" />
          <span class="ms-2 text-sm text-gray-600 dark:text-slate-300">{{ __('auth.login.form.remember_me') }}</span>
        </label>
      </div>

      <div class="block mt-4">
        <Button
          class="w-full"
          :loading="form.processing"
          :label="__('auth.login.form.submit')"
          @click="submit"
        />
      </div>

      <div class="block mt-4 text-center">
        <Link
          v-if="canResetPassword"
          :href="route('password.request')"
          class="text-l font-bold text-gray-600 hover:text-gray-900 dark:text-slate-300 dark:hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          {{ __('auth.login.links.restore_password') }}
        </Link>
      </div>
    </form>
  </GuestLayout>
</template>
