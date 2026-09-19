<script setup>
import { ref, computed, onMounted } from 'vue';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionCheckbox from '@Core/Components/Collection/CollectionCheckbox.vue';

import PlantDetailService from '@Fields/Services/PlantDetailService';
import { stringToFormat } from '@Core/Utils/date';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  plant_id: {
    type: Number,
    default: null,
  },
  quarter_id: {
    type: Number,
    default: null,
  },
  field_id: {
    type: Number,
    default: null,
  },
  show_harvests: {
    type: Boolean,
    default: true,
  },
  harvest_available_years: {
    type: Array,
    default: [],
  },
});

const loading = ref(true);
const details = ref([]);
const year_options = ref([{ value: null, text: 'Todos' }, ...props.harvest_available_years]);
const selectedYear = ref(year_options.value[0]);

const categories = ref([
  { name: 'harvest', key: 'harvest' },
  { name: 'height', key: 'height' },
  { name: 'crown_diameter', key: 'crown_diameter' },
  { name: 'trunk_diameter', key: 'trunk_diameter' },
  { name: 'root_diameter', key: 'root_diameter' },
  { name: 'invasion_radius', key: 'invasion_radius' },
  { name: 'foliage_sanitation', key: 'foliage_sanitation' },
  { name: 'foliage_sanitation_photo', key: 'foliage_sanitation_photo' },
  { name: 'trunk_sanitation', key: 'trunk_sanitation' },
  { name: 'trunk_sanitation_photo', key: 'trunk_sanitation_photo' },
  { name: 'soil_sanitation', key: 'soil_sanitation' },
  { name: 'soil_sanitation_photo', key: 'soil_sanitation_photo' },
  { name: 'irrigation_system', key: 'irrigation_system' },
]);

const selectedCategories = ref(categories.value.map((category) => category.name));
const selectAll = ref(['all']);
const selectAllHandler = async () => {
  selectedCategories.value = selectAll.value.includes('all') ? categories.value.map((category) => category.name) : [];
};

const selectHandler = async () => {
  selectAll.value = selectedCategories.value.length === categories.value.length ? ['all'] : [];
};

const detailsFiltered = computed(() => {
  return details.value.filter((detail) => selectedCategories.value.includes(detail.type));
});

const filter = async () => {
  if (props.plant_id) {
    details.value = await PlantDetailService.listByPlantId(props.plant_id, selectedYear?.value?.value, props.show_harvests);
  } else if (props.quarter_id) {
    details.value = await PlantDetailService.listByQuarterId(props.quarter_id, selectedYear?.value?.value, props.show_harvests);
  } else if (props.field_id) {
    details.value = await PlantDetailService.listByFieldId(props.field_id, selectedYear?.value?.value, props.show_harvests);
  }
};

onMounted(async () => {
  await filter();
  loading.value = false;
});

const openImage = (image) => {
  window.open(image, '_blank');
};

defineExpose({ filter });
</script>

<template>
  <div class="grid grid-cols-4 gap-4">
    <CollectionCardSection
      wrapperClass="p-5"
    >
      <div class="mb-4">
        <div class="text-gray-400 pb-1">Año de cosecha</div>
        <CollectionSelect
          v-model="selectedYear"
          :placeholder="__('generics.please_select')"
          :options="year_options"
          @change="filter"
        />
      </div>
      <div class="justify-center">
        <CollectionCheckbox v-model="selectAll" :inputId="`all-categories`" name="category" value="all" @change="selectAllHandler" />
        <label class="ms-2" :for="`all-categories`">{{ __('harvest_details.all_categories') }}</label>

        <span class="ms-8 text-surface-500 dark:text-surface-400">{{ __('harvest_details.selected', { size: selectedCategories.length }) }}</span>

        <div v-for="category of categories" :key="category.key" class="flex items-center gap-2 my-4">
          <CollectionCheckbox v-model="selectedCategories" :inputId="category.key" name="category" :value="category.name" @change="selectHandler" />
          <label :for="category.key">
            {{ __(`harvest_details.form.${category.name}.label`) }}
            <!--
            <div
              class="w-4 h-4 mt-1 ms-2 border-2 border-gray-500 rounded-full float-end"
              :style="{ backgroundColor: __(`harvest_details.form.${category.name}.color`) }"
            ></div>
            -->
          </label>
        </div>
      </div>
    </CollectionCardSection>
    <CollectionCardSection
      wrapperClass="p-5"
      sectionClass="mt-5 col-span-3 rounded-xl shadow-sm ring-1 ring-gray-950/5"
    >
      <div v-if="loading">
        <div class="flex justify-center items-center h-screen">
          <div
            class="inline-block size-[50px] rounded-full border-4 border-[#e1e9e3] border-t-[#17663a] animate-spin"
            role="status"
            aria-label="Cargando"
          ></div>
        </div>
      </div>
      <div v-else-if="detailsFiltered.length > 0">
        <ol class="space-y-6">
          <li
            v-for="(item, idx) in detailsFiltered"
            :key="idx"
            class="flex gap-4"
          >
            <div class="shrink-0">
              <span
                class="flex size-8 items-center justify-center text-white rounded-full shadow-sm"
                :style="{ backgroundColor: __(`harvest_details.form.${item.type}.color`) }"
              >
                <span class="size-3 rounded-full bg-white"></span>
              </span>
            </div>
            <div class="shrink-0 w-40 text-sm text-[#61716c]">
              <div>{{ item.plant_code }}</div>
              <div>{{ stringToFormat(item.updated_at, 'dd/MM/yyyy HH:mm') }}</div>
              <div v-if="item.note">Nota: {{ item.note }}</div>
            </div>
            <div class="grow">
              {{ __(`harvest_details.form.${item.type}.label`) }}
              <div v-if="item.type.endsWith('_photo')">
                <img
                  :src="item.value"
                  class="w-1/2 h-auto cursor-pointer"
                  @click="openImage(item.value)"
                >
              </div>
              <div v-else>
                {{ __(`harvest_details.form.${item.type}.unit`) ? formatNumber(item.value) : item.value }}
                <span v-if="__(`harvest_details.form.${item.type}.unit`)">
                  ({{ __(`harvest_details.form.${item.type}.unit`) }})
                </span>
              </div>
            </div>
          </li>
        </ol>
      </div>
      <div v-else class="my-20">
        <p class="text-center text-surface-500 dark:text-surface-400">
          {{ __('harvest_details.no_data') }}
        </p>
      </div>
    </CollectionCardSection>
  </div>
</template>
