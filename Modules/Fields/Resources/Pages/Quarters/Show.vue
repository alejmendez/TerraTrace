<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from 'primevue/useconfirm';
import { deleteRowTable } from '@Core/Utils/table';
import { can } from '@Auth/Services/Auth';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import StatisticsCard from '@Fields/Pages/Quarters/ShowComponents/StatisticsCard.vue';
import HarvestCard from '@Fields/Pages/Quarters/ShowComponents/HarvestCard.vue';
import LogbookCard from '@Fields/Pages/Quarters/ShowComponents/LogbookCard.vue';
import FileCard from '@Fields/Pages/Quarters/ShowComponents/FileCard.vue';

import Button from '@Core/Components/Form/Button.vue';

const confirm = useConfirm();

const props = defineProps({
  data: Object,
  current_tab: String,
  harvest_available_years: Array,
  harvest_available_weeks: Array,
  fields: Array,
  quarters: Array,
  users: Array,
  scale_types: Array,
});

const { data: quarter } = props.data;

const canDestroy = can('quarters.destroy');
const canEdit = can('quarters.edit');

const FILE_TAB = 'file';
const LOGBOOK_TAB = 'logbook';
const HARVEST_TAB = 'harvest';
const STATISTICS_TAB = 'statistics';

const tabs = [FILE_TAB, LOGBOOK_TAB, HARVEST_TAB, STATISTICS_TAB];

const current_tab = ref(props.current_tab || FILE_TAB);

const isFileTab = computed(() => current_tab.value === FILE_TAB);
const isLogbookTab = computed(() => current_tab.value === LOGBOOK_TAB);
const isHarvestTab = computed(() => current_tab.value === HARVEST_TAB);
const isStatisticsTab = computed(() => current_tab.value === STATISTICS_TAB);

const selectTab = (tab) => {
  current_tab.value = tab;
  const url = new URL(window.location.href);
  url.searchParams.set('current_tab', tab);
  window.history.pushState({}, '', url);
};

const deleteHandler = async (id) => {
  await deleteRowTable(confirm, () => {
    router.delete(route('quarters.destroy', id));
  });
};
</script>

<template>
  <AuthenticatedLayout :title="__('quarter.titles.show', {name: quarter.name})">
    <HeaderCrud
      :title="__('quarter.titles.show', {name: quarter.name})"
      :breadcrumbs="[{ to: 'quarters.index', text: __('quarter.titles.entity_breadcrumb') }, { text: __('generics.detail') }]"
    >
      <Button
        severity="secondary"
        @click="deleteHandler(quarter.id)"
        :label="__('generics.actions.delete')"
        v-if="canDestroy"
        v-show="isFileTab"
      />
      <Button
        :href="route('quarters.edit', quarter.id)"
        :label="__('generics.actions.edit')"
        v-if="canEdit"
        v-show="isFileTab"
      />
    </HeaderCrud>

    <div class="flex place-content-center">
      <nav class="flex mb-1 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-700 px-4 py-1">
        <span
          v-for="tab of tabs"
          class="px-4 py-2 cursor-default font-semibold"
          :class="current_tab === tab ? 'text-(--p-primary-500)' : 'hover:text-(--p-primary-300) dark:hover:text-(--p-primary-600) text-gray-400'"
          @click="selectTab(tab)"
        >
          {{ __('quarter.show.tabs.' + tab) }}
        </span>
      </nav>
    </div>

    <FileCard
      :quarter="quarter"
      v-show="isFileTab"
    />

    <LogbookCard
      :quarter_id="quarter.id"
      :harvest_available_years="props.harvest_available_years"
      v-show="isLogbookTab"
    />

    <HarvestCard
      :quarter_id="quarter.id"
      :harvest_available_years="props.harvest_available_years"
      :harvest_available_weeks="props.harvest_available_weeks"
      :fields="props.fields"
      :quarters="props.quarters"
      :users="props.users"
      v-show="isHarvestTab"
    />

    <StatisticsCard
      :quarter="quarter"
      :scale_types="props.scale_types"
      v-show="isStatisticsTab"
    />
  </AuthenticatedLayout>
</template>
