<script setup>
import { computed } from 'vue';
import {
  Archive,
  ArrowDown,
  ArrowLeft,
  ArrowRight,
  ArrowUp,
  Barcode,
  ChartColumn,
  CheckSquare,
  ChevronDown,
  ChevronLeft,
  ChevronRight,
  ChevronUp,
  CircleAlert,
  CircleCheck,
  ClipboardList,
  Download,
  FilterX,
  Grid3x3,
  LayoutGrid,
  Leaf,
  List,
  Map,
  MapPin,
  MoreHorizontal,
  Mountain,
  PawPrint,
  Plus,
  ReceiptText,
  Search,
  Shapes,
  ShieldCheck,
  ShoppingBasket,
  Sprout,
  Square,
  Tractor,
  Trash2,
  UserRound,
  Users,
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
  /**
   * Aliases for menu icons that historically came from Material Symbols
   * and were stored in the `menus.icon` column as HTML strings like
   * `<span class="material-symbols-rounded">insert_chart</span>`. The
   * menu layer (MenuElement.vue) extracts the ligature name and looks
   * it up here, so these slugs keep the existing DB data compatible.
   */
  barcode: Barcode,
  border_all: Grid3x3,
  build: Wrench,
  category: Shapes,
  check_circle: CircleCheck,
  check_square: CheckSquare,
  checklist: ClipboardList,
  chevron_left: ChevronLeft,
  chevron_right: ChevronRight,
  close: X,
  delete: Trash2,
  download: Download,
  eco: Leaf,
  error: CircleAlert,
  expand_less: ChevronUp,
  expand_more: ChevronDown,
  filter_alt_off: FilterX,
  format_list_bulleted: List,
  grid_on: LayoutGrid,
  grid_view: LayoutGrid,
  groups: Users,
  insert_chart: ChartColumn,
  landscape: Mountain,
  location_on: MapPin,
  map: Map,
  more_horiz: MoreHorizontal,
  person: UserRound,
  pets: PawPrint,
  potted_plant: Sprout,
  request_quote: ReceiptText,
  search: Search,
  shield_with_heart: ShieldCheck,
  shopping_basket: ShoppingBasket,
  stop: Square,
  unarchive: Archive,
  wrench: Wrench,
};

const icon = computed(() => icons[props.name] || CircleAlert);
</script>

<template>
  <component :is="icon" :size="size" :stroke-width="strokeWidth" v-bind="$attrs" />
</template>
