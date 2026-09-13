<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from 'primevue/useconfirm';
import { deleteRowTable } from '@Core/Utils/table';
import { stringToFormat } from '@Core/Utils/date';
import { can } from '@Auth/Services/Auth';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import CardSection from '@Core/Components/CardSection.vue';

const confirm = useConfirm();

const props = defineProps({
  data: Object,
  current_tab: String,
});

const { data } = props.data;

const canDestroy = can('users.destroy');
const canEdit = can('users.edit');

const headerLinks = [];
if (canDestroy) {
  headerLinks.push({ to: () => deleteHandler(data.id), variant: 'secondary', text: 'generics.actions.delete' });
}
if (canEdit) {
  headerLinks.push({ to: route('users.edit', data.id), text: 'generics.actions.edit' });
}

const FILE_TAB = 'file';
const ACTIVITY_TAB = 'activity';
const STATISTICS_TAB = 'statistics';

const tabs = [FILE_TAB, ACTIVITY_TAB, STATISTICS_TAB];

const currentTab = ref(props.current_tab || FILE_TAB);

const isFileTab = computed(() => currentTab.value === FILE_TAB);
const isActivityTab = computed(() => currentTab.value === ACTIVITY_TAB);
const isStatisticsTab = computed(() => currentTab.value === STATISTICS_TAB);

const selectTab = (tab) => {
  currentTab.value = tab;
  const url = new URL(window.location.href);
  url.searchParams.set('current_tab', tab);
  window.history.pushState({}, '', url);
};

const deleteHandler = async (id) => {
  await deleteRowTable(confirm, () => {
    router.delete(route('users.destroy', id));
  });
};
</script>

<template>
  <AuthenticatedLayout :title="__('user.titles.show')">
    <HeaderCrud
      :title="__('user.titles.show')"
      :breadcrumbs="[{ to: 'users.index', text: __('user.titles.entity_breadcrumb') }, { text: __('generics.detail') }]"
      :links="headerLinks"
    />

    <div class="grid grid-cols-3 gap-4 auto-cols-max">
      <div class="card-section p-4 flex col-span-2 mt-5 rounded-xl shadow-sm ring-1 ring-gray-950/5">
        <div class="w-32 border rounded-md me-4">
          <img
            class="border rounded-md"
            :src="data.avatar"
            v-if="data.avatar"
            alt=""
          >
        </div>
        <div class="grow">
          <h3 class="my-4 text-2xl font-bold text-gray-900 dark:text-white">{{ data.full_name }}</h3>
          <div class="text-gray-400 dark:text-gray-400">{{ data.role.name }}</div>
        </div>
      </div>

      <div class="card-section p-4 mt-5 rounded-xl shadow-sm ring-1 ring-gray-950/5">
        <div class="text-gray-400 dark:text-gray-400 pb-1">{{ __('user.show.created_at') }}</div>
        <div class="pb-3 dark:text-white">{{ stringToFormat(data.created_at) }}</div>
        <div class="text-gray-400 dark:text-gray-400 pb-1">{{ __('user.show.updated_at') }}</div>
        <div class="dark:text-white">{{ stringToFormat(data.updated_at) }}</div>
      </div>
    </div>

    <div class="flex place-content-center mt-5">
      <nav class="flex mb-1 rounded-lg bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-700 px-4 py-1">
        <span
          v-for="tab of tabs"
          class="px-4 py-2 cursor-default font-semibold"
          :class="currentTab === tab ? 'text-(--p-primary-500)' : 'hover:text-(--p-primary-300) dark:hover:text-(--p-primary-600) text-gray-400'"
          @click="selectTab(tab)"
        >
          {{ __('user.show.tabs.' + tab) }}
        </span>
      </nav>
    </div>

    <CardSection
      :header-text="__('user.show.file.title')"
      wrapperClass="p-5 grid grid-cols-3 gap-4"
      v-show="isFileTab"
    >
      <div class="mb-2">
        <div class="text-gray-400 mb-2">{{ __('user.show.file.dni.label') }}</div>
        <div class="">{{ data.dni }}</div>
      </div>

      <div class="mb-2">
        <div class="text-gray-400 mb-2">{{ __('user.show.file.name.label') }}</div>
        <div class="">{{ data.name }}</div>
      </div>

      <div class="mb-2">
        <div class="text-gray-400 mb-2">{{ __('user.show.file.last_name.label') }}</div>
        <div class="">{{ data.last_name }}</div>
      </div>

      <div class="mb-2">
        <div class="text-gray-400 mb-2">{{ __('user.show.file.email.label') }}</div>
        <div class="">{{ data.email }}</div>
      </div>

      <div class="mb-2">
        <div class="text-gray-400 mb-2">{{ __('user.show.file.phone.label') }}</div>
        <div class="">{{ data.phone }}</div>
      </div>

      <div class="mb-2">
        <div class="text-gray-400 mb-2">{{ __('user.show.file.role.label') }}</div>
        <div class="">{{ data.role.name }}</div>
      </div>
    </CardSection>

    <CardSection
      :header-text="__('user.show.activity.title')"
      wrapperClass="p-5 grid grid-cols-2 gap-4"
      v-show="isActivityTab"
    >
    </CardSection>
    <CardSection
      :header-text="__('user.show.statistics.title')"
      wrapperClass="p-5 grid grid-cols-2 gap-4"
      v-show="isStatisticsTab"
    >
    </CardSection>
  </AuthenticatedLayout>
</template>
