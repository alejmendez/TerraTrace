<script setup>
import LogsCard from '@Fields/Pages/Plants/ShowComponents/LogsCard.vue';

const props = defineProps({
  quarter_id: Number,
  harvest_available_years: Array,
});
</script>

<template>
  <LogsCard :quarter_id="props.quarter_id" :harvest_available_years="props.harvest_available_years" />
</template>
