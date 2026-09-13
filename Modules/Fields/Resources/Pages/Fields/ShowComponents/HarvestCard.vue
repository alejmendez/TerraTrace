<script setup>
import HarvestTable from '@Fields/Pages/Harvests/Components/HarvestTable.vue';

const props = defineProps({
  field_id: Number,
  harvest_available_years: Array,
  harvest_available_weeks: Array,
  fields: Array,
  quarters: Array,
  users: Array,
});
</script>

<template>
  <div>
    <HarvestTable
      :field_id="props.field_id"
      :show_actions="false"
      :harvest_available_years="props.harvest_available_years"
      :harvest_available_weeks="props.harvest_available_weeks"
      :fields="props.fields"
      :quarters="props.quarters"
      :users="props.users"
    />
  </div>
</template>
