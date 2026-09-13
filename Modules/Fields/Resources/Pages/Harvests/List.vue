<script setup>
import { onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import Toast from 'primevue/toast';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import HarvestTable from '@Fields/Pages/Harvests/Components/HarvestTable.vue';
import { can } from '@Auth/Services/Auth';

const toast = useToast();

const props = defineProps({
  toast: Object,
  harvest_available_years: Array,
  harvest_available_weeks: Array,
  fields: Array,
  quarters: Array,
  users: Array,
});

const canCreate = can('harvests.create');

const headerLinks = [];
if (canCreate) {
  headerLinks.push({ to: 'harvests.create', text: 'generics.new' });
}

onMounted(() => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('harvest.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <Toast />

    <HarvestTable
      :harvest_available_years="props.harvest_available_years"
      :harvest_available_weeks="props.harvest_available_weeks"
      :fields="props.fields"
      :quarters="props.quarters"
      :users="props.users"
    />
  </AuthenticatedLayout>
</template>
