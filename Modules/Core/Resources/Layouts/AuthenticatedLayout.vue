<script setup>
import { usePage, Head, Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import Drawer from 'primevue/drawer';

import CollectionConfirmDialog from '@Core/Components/Collection/CollectionConfirmDialog.vue';
import CollectionToast from '@Core/Components/Collection/CollectionToast.vue';
import { useConfirm } from '@Core/Composables/useConfirm';
import { useToast } from '@Core/Composables/useToast';
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

const confirm = useConfirm();
const toast = useToast();
</script>

<template>
  <Head :title="title" />

  <div class="terra-app-shell">
    <div v-if="showSideBar" class="terra-sidebar-backdrop" @click="sideBarStore.close" />

    <aside class="terra-sidebar" :class="{ 'terra-sidebar--closed': !showSideBar }">
      <div class="terra-brand">
        <div class="terra-brand-mark" aria-hidden="true">
          <span class="material-symbols-rounded">spa</span>
        </div>
        <div>
          <p class="terra-brand-name">TerraTrace</p>
          <p class="terra-brand-subtitle">Gestión de cosecha</p>
        </div>
      </div>

      <SideBarLeft />

      <div class="terra-sidebar-footer">
        <span class="material-symbols-rounded" aria-hidden="true">eco</span>
        <p>Trazabilidad para<br>mejores decisiones</p>
        <i aria-hidden="true"></i>
      </div>
    </aside>

    <section class="terra-content">
      <header class="terra-topbar">
        <button
          class="terra-menu-toggle"
          type="button"
          aria-label="Alternar navegación"
          @click="sideBarStore.toggle"
        >
          <span class="material-symbols-rounded">menu</span>
        </button>

        <label class="terra-global-search">
          <span class="material-symbols-rounded" aria-hidden="true">search</span>
          <input type="search" placeholder="Buscar predios, lotes o tareas..." aria-label="Buscar registros">
        </label>

        <div class="terra-topbar-actions">
          <MenuUser />
        </div>
      </header>

      <main class="terra-main">
        <slot></slot>
      </main>
    </section>
  </div>
  <Drawer
    v-model:visible="showDrawerRightMenu"
    header="Administrar"
    position="right"
    @hide="drawerRightMenuStore.close"
  >
    <ul class="space-y-1">
      <li v-for="item in menuRightItems" :key="item.link">
        <Link
          :href="item.link"
          class="flex items-center rounded-lg px-3 py-2 text-[#315347] transition-colors hover:bg-[#edf5ed] hover:text-[#17663a]"
          @click="drawerRightMenuStore.close"
        >
          <span class="material-symbols-rounded me-3" v-if="item.icon">{{ item.icon }}</span>
          {{ __(item.text) }}
        </Link>
      </li>
    </ul>
  </Drawer>

  <CollectionConfirmDialog
    :visible="confirm.visible.value"
    :title="confirm.title.value"
    :message="confirm.message.value"
    @cancel="confirm.cancel"
    @confirm="confirm.confirm"
  />

  <CollectionToast
    :message="toast.message.value"
    :tone="toast.tone.value"
    @dismiss="toast.dismiss"
  />
</template>
