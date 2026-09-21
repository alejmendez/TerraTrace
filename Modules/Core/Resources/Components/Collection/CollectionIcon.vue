<script setup>
import { computed } from 'vue';
import {
  ArrowDown,
  ArrowLeft,
  ArrowRight,
  ArrowUp,
  CheckSquare,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ChevronUp,
  CircleAlert,
  CircleCheck,
  Download,
  FilterX,
  LayoutGrid,
  Map,
  MapPin,
  MoreHorizontal,
  Mountain,
  PawPrint,
  Plus,
  Search,
  Shapes,
  Sprout,
  Square,
  Tractor,
  Trash2,
  UserRound,
  Wrench,
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
  arrow_downward: ArrowDown,
  arrow_left: ArrowLeft,
  arrow_right: ArrowRight,
  arrow_upward: ArrowUp,
  category: Shapes,
  check_circle: CircleCheck,
  check_square: CheckSquare,
  chevron_left: ChevronLeft,
  chevron_right: ChevronRight,
  close: X,
  delete: Trash2,
  download: Download,
  error: CircleAlert,
  expand_less: ChevronUp,
  expand_more: ChevronDown,
  filter_alt_off: FilterX,
  grid_view: LayoutGrid,
  landscape: Mountain,
  location_on: MapPin,
  map: Map,
  more_horiz: MoreHorizontal,
  person: UserRound,
  pets: PawPrint,
  potted_plant: Sprout,
  search: Search,
  stop: Square,
  wrench: Wrench,
};

const icon = computed(() => icons[props.name] || CircleAlert);
</script>

<template>
  <component :is="icon" :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
