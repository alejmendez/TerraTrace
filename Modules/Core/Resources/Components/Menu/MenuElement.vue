<script setup>
import { Link } from '@inertiajs/vue3';

import { useSideBarStore } from '@Core/Stores/sidebar.js';

defineOptions({ inheritAttrs: false });

const props = defineProps({
  link: {
    type: String,
    required: true,
  },
  text: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    default: 'fa-solid fa-circle',
  },
  active: {
    type: Boolean,
    default: false,
  },
});

const sideBarStore = useSideBarStore();

const closeSideBarOnMobile = () => {
  if (window.matchMedia('(max-width: 1023px)').matches) {
    sideBarStore.close();
  }
};
</script>

<template>
  <Link
    :href="props.link"
    class="menu-element"
    :class="{ active: props.active }"
    @click="closeSideBarOnMobile"
  >
    <div class="w-[25px] mr-2 flex justify-center">
      <span v-html="props.icon"></span>
    </div>
    {{ props.text }}
  </Link>
</template>

<style>
.menu-element .fa-circle {
  font-size: 6px;
}
</style>
