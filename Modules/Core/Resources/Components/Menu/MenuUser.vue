<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { usePreset } from '@primevue/themes';

import Presents from '@Core/Libs/PrimePresents';
import { useDrawerRightMenuStore } from '@Core/Stores/sidebar.js';
import { canShowRightMenu } from '@Auth/Services/Auth';

import MenuNotification from './MenuNotification.vue';

const root = ref(null);
const drawerRightMenuStore = useDrawerRightMenuStore();

const showDropDown = ref(false);
const showRightMenu = ref(canShowRightMenu());

const themes = [
    { name: 'Apple', class: 'bg-[#16a34a]' },
    { name: 'Cobalt', class: 'bg-[#1e40af]' },
    { name: 'DodgerBlue', class: 'bg-[#3b82f6]' },
    { name: 'Vulcan', class: 'bg-[#18181b]' },
    { name: 'CarrotOrange', class: 'bg-[#f59e0b]' },
];

const darkMode = ref('light');

const toggleDropMenu = () => {
    drawerRightMenuStore.toggle();
};

const toggleDrop = () => {
    showDropDown.value = !showDropDown.value;
};

const closeDropDown = (e) => {
    if (root.value && !root.value.contains(e.target)) {
        showDropDown.value = false;
    }
};

const toggleTheme = (isDark) => {
    localStorage.themeType = isDark ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', isDark);
};

const setDarkMode = (value) => {
    darkMode.value = value;
    toggleTheme(value === 'dark');
};

const setTheme = (theme) => {
    localStorage.setItem('theme', theme.name);
    usePreset(Presents[theme.name]);
};

const storedThemeType = localStorage.getItem('themeType');
const shouldUseDarkTheme = storedThemeType === null
    ? window.matchMedia('(prefers-color-scheme: dark)').matches
    : storedThemeType === 'dark';

darkMode.value = shouldUseDarkTheme ? 'dark' : 'light';
toggleTheme(shouldUseDarkTheme);

onMounted(() => {
    document.addEventListener('click', closeDropDown);
});

onUnmounted(() => {
    document.removeEventListener('click', closeDropDown);
});
</script>

<template>
    <div v-if="showRightMenu" class="relative me-3">
        <div
            class="text-lg w-[40px] h-[40px] cursor-pointer hover:bg-[#eff7ef] dark:hover:bg-[#203a2a] text-[#294a3c] dark:text-[#dcebdd] pt-2 ps-2 rounded-full transition-all ease-out duration-300"
            @click="toggleDropMenu"
        >
            <span class="material-symbols-rounded">settings</span>
        </div>
    </div>

    <MenuNotification />

    <div ref="root" class="w-[40px]">
        <div
            class="flex items-center justify-start space-x-4"
            @click="toggleDrop"
        >
            <img
                class="w-10 h-10 rounded-full border-2 border-gray-50"
                :src="$page.props.auth.user.avatar_url"
                alt=""
            >
        </div>
        <div
            v-show="showDropDown"
            class="absolute right-[10px] z-50 mt-2 w-56 origin-top-right rounded-lg bg-white dark:bg-[#1b2d22] shadow-lg ring-1 ring-black/5 dark:ring-[#496752] focus:outline-none"
            role="menu"
            aria-orientation="vertical"
            aria-labelledby="menu-button"
            tabindex="-1"
        >
            <div class="text-[#1d3d30] dark:text-[#edf6ed] font-semibold text-left block px-4 py-2">
                <div>{{ $page.props.auth.user.full_name }}</div>
            </div>
            <div class="px-4 py-2">
                <div
                    role="group"
                    aria-label="Tema"
                    class="flex overflow-hidden rounded-lg border border-[#d7e0d9]"
                >
                    <button
                        type="button"
                        class="flex-1 px-3 py-2 text-sm transition"
                        :class="darkMode === 'dark'
                            ? 'bg-[#17663a] text-white'
                            : 'bg-white text-[#284238] hover:bg-[#edf5ed]'"
                        :aria-pressed="darkMode === 'dark'"
                        @click="setDarkMode('dark')"
                    >
                        <span class="material-symbols-rounded">dark_mode</span>
                    </button>
                    <button
                        type="button"
                        class="flex-1 px-3 py-2 text-sm transition border-l border-[#d7e0d9]"
                        :class="darkMode === 'light'
                            ? 'bg-[#17663a] text-white'
                            : 'bg-white text-[#284238] hover:bg-[#edf5ed]'"
                        :aria-pressed="darkMode === 'light'"
                        @click="setDarkMode('light')"
                    >
                        <span class="material-symbols-rounded">light_mode</span>
                    </button>
                </div>
            </div>
            <div class="px-4 py-2 mb-4">
                <div
                    v-for="theme in themes"
                    :key="theme.name"
                    :class="`${theme.class} rounded-full w-4 h-4 float-start me-2 cursor-pointer`"
                    role="button"
                    :aria-label="theme.name"
                    tabindex="0"
                    @click="setTheme(theme)"
                    @keydown.enter="setTheme(theme)"
                ></div>
            </div>
            <div class="py-1 text-left" role="none">
                <Link
                    :href="route('profile.edit')"
                    class="text-[#355448] dark:text-[#d4e6d6] block px-4 py-2 hover:bg-[#f1f7f1] dark:hover:bg-[#203a2a]"
                >
                    {{ __('menu.top.profile') }}
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-[#355448] dark:text-[#d4e6d6] block px-4 py-2 hover:bg-[#f1f7f1] dark:hover:bg-[#203a2a] w-full text-left"
                >
                    {{ __('menu.top.logout') }}
                </Link>
            </div>
        </div>
    </div>
</template>
