<script setup>
import { storeToRefs } from 'pinia';
import SideBarLeft from '@Core/Components/Menu/SideBarLeft.vue';

import { useSideBarStore } from '@Core/Stores/sidebar.js';

const sideBarStore = useSideBarStore();
const { show } = storeToRefs(sideBarStore);
</script>

<template>
  <div class="flex items-center w-full h-[64px] bg-slate-900 text-gray-100 px-[20px] py-[10px] z-30">
    <div class="w-[230px]">
      <div
      class="w-[36px] inline rounded outline-none transition duration-75 text-gray-100 hover:text-red-500 focus-visible:ring-primary-600 border border-white hover:border-red-500 px-3 py-1"
        @click="sideBarStore.toggle"
      >
        <span class="material-symbols-rounded !text-md">dehaze</span>
      </div>
    </div>
  </div>
  <div class="w-full flex bg-gray-100 font-normal text-gray-900 antialiased">
    <div
      class="flex-none transition-all duration-200 ease-out z-10 border-r bg-slate-900 text-gray-100"
      :class="{ 'lg:w-[320px] lg:opacity-100 w-[0px] opacity-0': !show, 'lg:w-[0px] lg:opacity-0 w-full opacity-100': show }"
    >
      <SideBarLeft />
    </div>
    <div class="grow z-20">
      <div class="min-h-[calc(100vh-64px)] bg-gray-100 pb-5">
        <main class="py-[10px] px-[25px]">
          <slot></slot>
        </main>
      </div>
    </div>
  </div>
</template>
