<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

import MenuElement from './MenuElement.vue';
import { menuElements } from '@Core/Services/Menu.js';

const page = usePage();
const currentComponent = page.component;

const menuData = menuElements(currentComponent);
const initialState = menuData.map((_, index) => index);
const menuState = ref(JSON.parse(localStorage.getItem('menu-state')) || initialState);

const openHandler = (e) => {
  localStorage.setItem('menu-state', JSON.stringify(e));
};
</script>
<template>
  <aside class="aside-left-menu min-h-[calc(100vh-50px)] ps-[15px] pe-[20px]">
    <div class="flex flex-col justify-between space-y-[10px] mt-3 mb-10">
      <Accordion :value="menuState" multiple @update:value="openHandler">
        <AccordionPanel :value="index" v-for="(menu, index) in menuData" :key="index">
          <div v-if="menu.link">
            <MenuElement
              :link="menu.link"
              :text="menu.text"
              :icon="menu.icon"
              :active="menu.active"
            />
          </div>
          <div v-else>
            <AccordionHeader>{{ menu.text }}</AccordionHeader>
            <AccordionContent>
              <MenuElement
                v-for="(ele, indexChild) in menu.children"
                :key="indexChild"
                :link="ele.link"
                :text="ele.text"
                :icon="ele.icon"
                :active="ele.active"
              />
            </AccordionContent>
          </div>
        </AccordionPanel>
      </Accordion>
    </div>
  </aside>
</template>
