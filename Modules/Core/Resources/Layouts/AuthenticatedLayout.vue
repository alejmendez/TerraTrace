<script setup>
import { usePage, Head, Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import ConfirmDialog from 'primevue/confirmdialog';
import Toast from 'primevue/toast';
import Drawer from 'primevue/drawer';

import SideBarLeft from '@Core/Components/Menu/SideBarLeft.vue';
import MenuUser from '@Core/Components/Menu/MenuUser.vue';
import { useSideBarStore } from '@Core/Stores/sidebar.js';
import { useDrawerRightMenuStore } from '@Core/Stores/sidebar.js';
import { menuElementsRight } from '@Core/Services/Menu';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
});

const page = usePage();
const currentComponent = page.component;

const sideBarStore = useSideBarStore();
const { show: showSideBar } = storeToRefs(sideBarStore);

const drawerRightMenuStore = useDrawerRightMenuStore();
const { show: showDrawerRightMenu } = storeToRefs(drawerRightMenuStore);

const menuRightItems = menuElementsRight(currentComponent);
</script>

<template>
  <Head :title="title" />

  <div class="flex items-center w-full h-[64px] bg-gray-50 dark:bg-[#2F3349] text-gray-100 px-[20px] py-[10px] z-30">
    <div class="w-[230px] select-none">
      <div
        class="px-2 pt-3 inline rounded outline-none transition duration-75 text-(color:--p-primary-500) hover:text-(color:--p-primary-700) focus-visible:ring-primary-600 border border-(color:--p-primary-500) hover:border-(color:--p-primary-700)"
        @click="sideBarStore.toggle"
      >
        <span class="material-symbols-rounded !text-md">dehaze</span>
      </div>
    </div>

    <div class="w-[calc(100%-30px)] flex justify-end">
      <MenuUser />
    </div>
  </div>
  <div class="w-full flex font-normal text-gray-900 antialiased">
    <div
      class="flex-none transition-all duration-200 ease-out z-10 bg-gray-50 dark:bg-[#2F3349] text-gray-100"
      :class="{ 'lg:w-[320px] lg:opacity-100 w-[0px] opacity-0': !showSideBar, 'lg:w-[0px] lg:opacity-0 w-full opacity-100': showSideBar }"
    >
      <h3 class="font-bold text-xl inline ms-6 mt-4">
        <span class="text-(color:--p-primary-color)">SW </span>
        <span class="text-gray-900 dark:text-gray-50">Agricola</span>
      </h3>
      <SideBarLeft />
    </div>
    <div class="grow z-20 bg-zinc-200 dark:bg-[#1D2132]">
      <div class="min-h-[calc(100vh-64px)] pb-5">
        <main class="py-[10px] px-[25px] w-full">
          <Toast />
          <slot></slot>
        </main>
      </div>
    </div>
  </div>
  <Drawer
    v-model:visible="showDrawerRightMenu"
    header="Administrar"
    position="right"
    @hide="drawerRightMenuStore.close"
  >
    <div class="">
      <ul class="space-y-1">
        <li v-for="item in menuRightItems" :key="item.link">
          <Link
            :href="item.link"
            class="flex items-center py-2 px-2 border-s-4 border-(--p-primary-color) hover:border-primary-500 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-100 hover:text-primary-500 transition-colors"
            @click="drawerRightMenuStore.close"
          >
            <span class="material-symbols-rounded me-3" v-if="item.icon">{{ item.icon }}</span>
            {{ __(item.text) }}
          </Link>
        </li>
      </ul>
    </div>
  </Drawer>

  <ConfirmDialog></ConfirmDialog>
</template>
