<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

const root = ref(null);
const page = usePage();
const unread_notifications = page.props.auth.user.unread_notifications || [];

const showDropDown = ref(false);

const numberOfNotifications = ref(unread_notifications.length);

const toggleDrop = () => {
  showDropDown.value = !showDropDown.value;
};

const closeDropDown = (e) => {
  if (!root.value.contains(e.target)) {
    showDropDown.value = false;
  }
};

onMounted(() => {
  document.addEventListener('click', closeDropDown);
});

onUnmounted(() => {
  document.removeEventListener('click', closeDropDown);
});
</script>

<template>
  <div class="relative me-3" ref="root">
    <div
      class="text-lg w-[40px] h-[40px] cursor-pointer hover:bg-gray-300 dark:hover:bg-gray-700 text-gray-900 dark:text-gray-100 pt-2 ps-2 rounded-full transition-all ease-out duration-300"
      @click="toggleDrop"
    >
      <span class="material-symbols-rounded">notifications</span>
      <span
        class="text-xs bg-orange-700 text-gray-100 rounded-full px-1 py-0 absolute top-2 right-2"
        v-if="numberOfNotifications > 0"
      >
        {{ numberOfNotifications }}
      </span>
    </div>
  </div>
  <div
    v-show="showDropDown"
    class="absolute right-[60px] z-50 mt-12 w-96 origin-top-right rounded-md text-black dark:text-gray-100 bg-white dark:bg-gray-900 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
    tabindex="-1"
  >
    <div class="font-semibold text-left block px-4 py-2">
      <div
        v-if="numberOfNotifications === 0"
      >
        🥳 No tienes notificaciones pendientes
      </div>
      <div v-else>
        <ul>
          <li v-for="notification in unread_notifications">
            <div class="material-symbols-rounded text-sky-600 !text-[20px]">info</div>
            <Link :href="route('tasks.show', notification.data.task_id)">
              Hay una actualizacion en la tarea {{ notification.data.task_name }}
            </Link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
