<script setup>
import { usePage, Head, Link } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';

import CollectionConfirmDialog from '@Core/Components/Collection/CollectionConfirmDialog.vue';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';
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
          <CollectionIcon name="spa" :size="20" />
        </div>
        <div>
          <p class="terra-brand-name">TerraTrace</p>
          <p class="terra-brand-subtitle">Gestión de cosecha</p>
        </div>
      </div>

      <SideBarLeft />

      <div class="terra-sidebar-footer">
        <CollectionIcon name="eco" :size="20" aria-hidden="true" />
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
          <CollectionIcon name="menu" :size="22" />
        </button>

        <label class="terra-global-search">
          <CollectionIcon name="search" :size="20" class="text-[#61716c]" aria-hidden="true" />
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
  <Teleport to="body">
    <div
      v-if="showDrawerRightMenu"
      class="fixed inset-0 z-40 flex justify-end bg-[#102f27]/35 p-0"
      role="presentation"
      @click="drawerRightMenuStore.close"
    >
      <aside
        class="flex h-full w-[280px] max-w-full flex-col bg-white shadow-2xl"
        role="dialog"
        aria-modal="true"
        aria-label="Administrar"
        @click.stop
      >
        <header class="flex shrink-0 items-center justify-between border-b border-[#e1e9e3] px-5 py-4">
            <h2 class="text-lg font-extrabold text-[#102f27]">
                Administrar
            </h2>
            <button
                type="button"
                class="text-[#61716c] hover:text-[#102f27]"
                aria-label="Cerrar"
                @click="drawerRightMenuStore.close"
            >
                <CollectionIcon name="close" :size="22" />
            </button>
        </header>
        <ul class="grow space-y-1 overflow-y-auto p-3">
            <li v-for="item in menuRightItems" :key="item.link">
                <Link
                    :href="item.link"
                    class="flex items-center rounded-lg px-3 py-2 text-[#315347] transition-colors hover:bg-[#edf5ed] hover:text-[#17663a]"
                    @click="drawerRightMenuStore.close"
                >
                    <CollectionIcon
                        v-if="item.icon"
                        :name="item.icon"
                        :size="20"
                        class="me-3"
                    />
                    {{ __(item.text) }}
                </Link>
            </li>
        </ul>
      </aside>
    </div>
  </Teleport>

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
