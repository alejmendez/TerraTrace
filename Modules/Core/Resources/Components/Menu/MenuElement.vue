<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import { useSideBarStore } from '@Core/Stores/sidebar.js';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';

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
    default: '',
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

/**
 * `icon` historically arrives as an HTML string from the `menus.icon`
 * column (e.g. `<span class="material-symbols-rounded">insert_chart</span>`).
 * Extract the ligature name so it can be looked up in CollectionIcon.
 * Returns `null` if the prop doesn't match that shape (e.g. an empty
 * string or a plain slug already), which falls back to no icon rather
 * than rendering raw HTML.
 */
const iconName = computed(() => {
  const raw = props.icon;
  if (!raw) {
    return null;
  }
  const match = raw.match(/>([^<]+)</);
  if (match) {
    return match[1].trim();
  }
  // Plain slug without HTML wrapping — pass through directly.
  const trimmed = raw.trim();
  return trimmed === '' ? null : trimmed;
});
</script>

<template>
  <Link
    :href="props.link"
    class="menu-element"
    :class="{ active: props.active }"
    @click="closeSideBarOnMobile"
  >
    <div class="w-[25px] flex justify-center">
      <CollectionIcon v-if="iconName" :name="iconName" :size="18" />
    </div>
    {{ props.text }}
  </Link>
</template>

<style>
.menu-element .fa-circle {
  font-size: 6px;
}
</style>
