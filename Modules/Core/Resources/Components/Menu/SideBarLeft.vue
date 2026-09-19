<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

import MenuElement from './MenuElement.vue';
import { menuElements } from '@Core/Services/Menu.js';

const page = usePage();
const currentComponent = page.component;

const menuData = menuElements(currentComponent);
const initialState = menuData.map((_, index) => index);
const savedMenuState = localStorage.getItem('menu-state');

const getMenuState = () => {
    if (!savedMenuState) {
        return initialState;
    }

    try {
        const parsedMenuState = JSON.parse(savedMenuState);

        return Array.isArray(parsedMenuState) ? parsedMenuState : initialState;
    } catch {
        return initialState;
    }
};

const menuState = ref(getMenuState());

watch(menuState, (value) => {
    localStorage.setItem('menu-state', JSON.stringify(value));
});

function togglePanel(index) {
    const idx = menuState.value.indexOf(index);
    if (idx === -1) {
        menuState.value = [...menuState.value, index];
    } else {
        menuState.value = menuState.value.filter((i) => i !== index);
    }
}

function isOpen(index) {
    return menuState.value.includes(index);
}
</script>

<template>
    <aside class="aside-left-menu min-h-[calc(100vh-50px)] ps-[15px] pe-[20px]">
        <div class="flex flex-col justify-between space-y-[10px] mt-3 mb-10">
            <div v-for="(menu, index) in menuData" :key="index">
                <div v-if="menu.link">
                    <MenuElement
                        :link="menu.link"
                        :text="menu.text"
                        :icon="menu.icon"
                        :active="menu.active"
                    />
                </div>
                <div v-else>
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-[#284238] hover:bg-[#edf5ed] dark:text-[#dcebdd] dark:hover:bg-[#203a2a]"
                        :aria-expanded="isOpen(index)"
                        @click="togglePanel(index)"
                    >
                        <span class="text-sm font-semibold">{{ menu.text }}</span>
                        <span class="material-symbols-rounded">
                            {{ isOpen(index) ? 'expand_less' : 'expand_more' }}
                        </span>
                    </button>
                    <div v-show="isOpen(index)" class="ps-4">
                        <MenuElement
                            v-for="(ele, indexChild) in menu.children"
                            :key="indexChild"
                            :link="ele.link"
                            :text="ele.text"
                            :icon="ele.icon"
                            :active="ele.active"
                        />
                    </div>
                </div>
            </div>
        </div>
    </aside>
</template>
