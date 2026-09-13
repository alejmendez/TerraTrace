<script setup>
import LogsCard from '@Fields/Pages/Plants/ShowComponents/LogsCard.vue';

const props = defineProps({
  field_id: Number,
  harvest_available_years: Array,
});
</script>

<template>
  <LogsCard :field_id="props.field_id" :harvest_available_years="props.harvest_available_years" />
</template>
