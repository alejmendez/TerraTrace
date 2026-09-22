<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';

import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';

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
      class="text-lg w-[40px] h-[40px] cursor-pointer hover:bg-[#eff7ef] dark:hover:bg-[#203a2a] text-[#294a3c] dark:text-[#dcebdd] pt-2 ps-2 rounded-full transition-all ease-out duration-300"
      @click="toggleDrop"
    >
      <CollectionIcon name="notifications" :size="22" />
      <span
        class="text-xs bg-[#e3a325] text-[#263216] rounded-full px-1 py-0 absolute top-2 right-2"
        v-if="numberOfNotifications > 0"
      >
        {{ numberOfNotifications }}
      </span>
    </div>
  </div>
  <div
    v-show="showDropDown"
    class="absolute right-[60px] z-50 mt-12 w-96 origin-top-right rounded-lg text-[#1d3d30] dark:text-[#edf6ed] bg-white dark:bg-[#1b2d22] shadow-lg ring-1 ring-black/5 dark:ring-[#496752] focus:outline-none"
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
            <CollectionIcon name="info" :size="20" class="text-sky-600" />
            <Link :href="route('tasks.show', notification.data.task_id)">
              Hay una actualizacion en la tarea {{ notification.data.task_name }}
            </Link>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>
