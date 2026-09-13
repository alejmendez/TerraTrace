<script setup>
import CardSection from '@Core/Components/CardSection.vue';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  quarter: Object,
});

const quarter = props.quarter;

const dataFile = [
  ['quarter.show.file.field', quarter.field.name],
  ['quarter.show.file.area', formatNumber(quarter.area)],
  ['quarter.show.file.plants_count', formatNumber(quarter.plants_count, 0)],
  ['quarter.show.file.responsible', quarter.responsible.name],
];
</script>

<template>
  <CardSection
    wrapperClass="p-5 grid grid-cols-2 gap-4"
  >
    <div>
      <img
        :src="quarter.blueprint"
        class="w-full"
        alt=""
      >
    </div>
    <div>
      <template v-for="block of dataFile">
        <div class="text-gray-400 pb-1">
          {{ __(block[0]) }}
        </div>
        <div class="pb-3 dark:text-gray-50">
          {{ block[1] }}
        </div>
      </template>
    </div>
  </CardSection>
</template>
