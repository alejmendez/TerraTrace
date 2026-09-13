<script setup>
import { computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';

import GuestLayout from '@Core/Layouts/GuestLayout.vue';
import PrimaryButton from '@Core/Components/PrimaryButton.vue';

const props = defineProps({
  status: {
    type: String,
  },
});

const form = useForm({});

const submit = () => {
  form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
  <GuestLayout>
    <Head :title="__('auth.verifyEmail.title')" />

    <div class="mb-4 text-sm text-gray-600">
      {{ __('auth.verifyEmail.subtitle') }}
    </div>

    <div class="mb-4 font-medium text-sm text-green-600" v-if="verificationLinkSent">
      {{ __('auth.verifyEmail.alert') }}
    </div>

    <form @submit.prevent="submit">
      <div class="mt-4 flex items-center justify-between">
        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
          {{ __('auth.verifyEmail.form.submit') }}
        </PrimaryButton>

        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          {{ __('auth.verifyEmail.form.logout') }}
        </Link>
      </div>
    </form>
  </GuestLayout>
</template>
