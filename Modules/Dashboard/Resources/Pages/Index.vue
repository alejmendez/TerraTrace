<script setup>
import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import FiltersDashboard from '@Dashboard/Components/FiltersDashboard.vue';
import CardsDashboard from '@Dashboard/Components/CardsDashboard.vue';
import MapDashboard from '@Dashboard/Components/MapDashboard.vue';
import MessageDashboard from '@Dashboard/Components/MessageDashboard.vue';

const props = defineProps({
  fields: Array,
  field: Object,
  harvest_data: Object,
  task_data: Object,
});
</script>

<template>
  <AuthenticatedLayout title="Dashboard">
    <FiltersDashboard :fields="props.fields" :field="props.field" />
    <CardsDashboard :field="props.field" :harvest_data="props.harvest_data" :task_data="props.task_data" />

    <section class="mt-5 grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 gap-4">
      <MapDashboard :field="props.field" />
      <MessageDashboard />
    </section>
  </AuthenticatedLayout>
</template>
