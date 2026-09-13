<script setup>
import CardSection from '@Core/Components/CardSection.vue';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  field: Object,
});

const field = props.field;

const fieldFile = [
  ['field.show.file.location', field.location],
  ['field.show.file.size', formatNumber(field.size)],
  ['field.show.file.plants_count', formatNumber(field.plants_count, 0)],
  ['field.show.file.quarters_count', field.quarters_count],
];
</script>

<template>
  <CardSection
    wrapperClass="p-5 grid grid-cols-2 gap-4"
  >
    <div>
      <img
        :src="field.blueprint"
        class="w-full"
        alt=""
      >
    </div>
    <div>
      <template v-for="block of fieldFile">
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
