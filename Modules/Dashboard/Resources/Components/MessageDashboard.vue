<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import ProgressSpinner from 'primevue/progressspinner';

import { listUnread } from '@Tasks/Services/TaskNotificationService';
import { stringToDate, relativeTimeDifference } from '@Core/Utils/date';

const props = defineProps({
  tasks: Array,
});

const messages = ref([]);
const today = new Date();
const countMessagesUnRead = ref(0);
const loading = ref(true);

const messagesRelativeTimeDifference = (message) => {
  return relativeTimeDifference(today, stringToDate(message.created_at));
};

onMounted(async () => {
  const response = await listUnread();
  messages.value = response.messages;
  countMessagesUnRead.value = response.countMessagesUnRead;
  loading.value = false;
});
</script>

<template>
  <div class="p-5 rounded-xl card-section">
    <div class="flex justify-between">
      <div class="font-bold">
        {{ __('dashboard.messages') }}
        <span
          v-show="countMessagesUnRead > 0"
          class="text-xs bg-orange-700 text-gray-100 rounded-full px-1">
          {{ countMessagesUnRead }}
        </span>
      </div>

      <div class="flex" v-show="!loading">
        <div class="me-5" v-show="messages.prev_page_url != null">
          <i class="pi pi-arrow-left hover:text-gray-500 dark:hover:text-gray-300"></i>
        </div>
        <div v-show="messages.next_page_url != null">
          <i class="pi pi-arrow-right hover:text-gray-500 dark:hover:text-gray-300"></i>
        </div>
      </div>
    </div>

    <div v-show="loading" class="text-center mt-5">
      <ProgressSpinner
        style="width: 50px; height: 50px"
        strokeWidth="8"
        fill="transparent"
        animationDuration=".5s"
        aria-label="Progress Spinner"
      />
    </div>

    <div class="mt-5" v-show="!loading && messages.total == 0">
      <div class="text-center">
        {{ __('dashboard.no_messages') }}
      </div>
    </div>

    <div class="mt-5" v-show="!loading" v-for="message in messages.data" :key="message.id">
      <Link :href="route('tasks.show', { id: message.task_id })" >
        <img
          class="w-[35px] rounded-full border-2 float-left me-2 mt-2"
          :class="{
            'border-(--p-primary-color)': message.read_at === null,
            'border-white': message.read_at !== null
          }"
          :src="message.notifier_user_avatar"
          alt=""
        />

        <div>
          <span class="text-gray-500 font-bold text-[12px] float-right" :class="{ 'font-bold': message.read_at === null }">{{ messagesRelativeTimeDifference(message) }}</span>
          <div
            class="line-clamp-2"
            :class="{
              'font-medium': message.read_at === null,
              'text-gray-500 dark:text-gray-300': message.read_at !== null
            }"
            :title="message.description"
          >
            {{ message.description }}
          </div>
        </div>
      </Link>
    </div>
  </div>
</template>
