<script setup>
import { ref } from 'vue';

import CardSection from '@Core/Components/CardSection.vue';
import Button from '@Core/Components/Form/Button.vue';

const props = defineProps({
  message_success: String,
  unprocessed_message: String,
  unprocessed_details: Array,
  error_message: String,
  errors: Array,
  title: String,
  downloadRoute: String,
});

const openAlert = ref(true);
const openErrors = ref(true);
const openUnprocesseds = ref(true);
</script>

<template>
  <CardSection :header-text="props.title" wrapperClass="">
    <div class="p-6">
      <p class="pb-3">{{ __('generics.bulk.instruction_1') }}</p>
      <ul class="list-decimal ps-5">
        <li>{{ __('generics.bulk.instruction_2') }}</li>
        <li>{{ __('generics.bulk.instruction_3') }}</li>
        <li>{{ __('generics.bulk.instruction_4') }}</li>
        <li>{{ __('generics.bulk.instruction_5') }}</li>
        <li>{{ __('generics.bulk.instruction_6') }}</li>
      </ul>
      <p class="py-3">
        <b>{{ __('generics.bulk.important') }}:</b>
        {{ __('generics.bulk.instruction_7') }}
      </p>

      <Button
        severity="outline"
        as="a"
        :href="route(props.downloadRoute)"
        target="_blank"
        :label="__('generics.bulk.download_template')"
      />
    </div>

    <slot />

    <div class="px-6 pb-6" v-if="props.message_success != '' && props.message_success != undefined && openAlert">
      <div class="p-4 rounded-lg bg-neutral-200 border border-slate-400 text-gray-700">
        <span class="material-symbols-rounded me-1 !text-[20px]">warning</span>
        {{ props.message_success }}
        <span class="material-symbols-rounded float-right !text-[18px] cursor-pointer" @click="openAlert = !openAlert">close</span>
      </div>
    </div>

    <div class="px-6 pb-6" v-if="props.unprocessed_message != '' && props.unprocessed_message != undefined && openUnprocesseds">
      <div class="p-4 rounded-lg bg-orange-200 border border-yellow-700 text-yellow-900">
        <span class="material-symbols-rounded me-1 !text-[20px]">warning</span>
        {{ props.unprocessed_message }}
        <span class="material-symbols-rounded float-right !text-[18px] cursor-pointer" @click="openUnprocesseds = !openUnprocesseds">close</span>
        <ul class="list-disc ps-5">
          <li v-for="detail in unprocessed_details" v-text="detail" />
        </ul>
      </div>
    </div>

    <div class="px-6 pb-6" v-if="props.error_message != '' && props.error_message != undefined && openErrors">
      <div class="p-4 rounded-lg bg-red-100 border border-red-300 text-stone-500">
        <span class="material-symbols-rounded me-1 !text-[20px]">warning</span>
        {{ props.error_message }}
        <span class="material-symbols-rounded float-right !text-[18px] cursor-pointer" @click="openErrors = !openErrors">close</span>
        <ul class="list-disc ps-5">
          <li v-for="error in props.errors">
            {{ error }}
          </li>
        </ul>
      </div>
    </div>
  </CardSection>
</template>
