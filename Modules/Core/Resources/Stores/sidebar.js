import { ref } from 'vue';
import { defineStore } from 'pinia';

const DEFAULT_SHOW_SIDE_BAR = true;
const DEFAULT_SHOW_DRAWER_RIGHT_MENU = false;
const LS_KEY_SHOW_SIDE_BAR = 'showSideBar';
const LS_KEY_SHOW_DRAWER_RIGHT_MENU = 'showDrawerRightMenu';

const getItem = (key, defaultValue) => localStorage.getItem(key) || defaultValue;
const setItem = (key, value) => localStorage.setItem(key, value);

export const useSideBarStore = defineStore('sideBar', () => {
  const show = ref(getItem(LS_KEY_SHOW_SIDE_BAR, DEFAULT_SHOW_SIDE_BAR) === 'true');

  const toggle = () => update(!show.value);
  const open = () => update(true);
  const close = () => update(false);

  const update = (status) => {
    show.value = status;
    setItem(LS_KEY_SHOW_SIDE_BAR, status);
  };

  return { show, toggle, open, close };
});

export const useDrawerRightMenuStore = defineStore('drawerRightMenu', () => {
  const show = ref(getItem(LS_KEY_SHOW_DRAWER_RIGHT_MENU, DEFAULT_SHOW_DRAWER_RIGHT_MENU) === 'true');

  const toggle = () => update(!show.value);
  const open = () => update(true);
  const close = () => update(false);

  const update = (status) => {
    show.value = status;
    setItem(LS_KEY_SHOW_DRAWER_RIGHT_MENU, status);
  };

  return { show, toggle, open, close };
});
