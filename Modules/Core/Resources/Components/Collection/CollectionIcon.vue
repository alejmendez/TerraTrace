<script setup>
import { computed } from 'vue';
import {
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ChevronUp,
  CircleAlert,
  CircleCheck,
  Download,
  LayoutGrid,
  Map,
  MapPin,
  MoreHorizontal,
  Mountain,
  Plus,
  Search,
  Shapes,
  Sprout,
  Tractor,
  Trash2,
  X,
} from '@lucide/vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
  },
  size: {
    type: [Number, String],
    default: 20,
  },
  strokeWidth: {
    type: [Number, String],
    default: 1.8,
  },
});

const icons = {
  add: Plus,
  agriculture: Tractor,
  category: Shapes,
  check_circle: CircleCheck,
  chevron_left: ChevronLeft,
  chevron_right: ChevronRight,
  close: X,
  delete: Trash2,
  download: Download,
  error: CircleAlert,
  expand_less: ChevronUp,
  expand_more: ChevronDown,
  grid_view: LayoutGrid,
  landscape: Mountain,
  location_on: MapPin,
  map: Map,
  more_horiz: MoreHorizontal,
  potted_plant: Sprout,
  search: Search,
};

const icon = computed(() => icons[props.name] || CircleAlert);
</script>

<template>
  <component :is="icon" :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
