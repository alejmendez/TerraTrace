<script setup>
import { ref, onMounted, useAttrs } from 'vue';

import DataTable from 'primevue/datatable';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';
import { trans } from 'laravel-vue-i18n';

import { useSideBarStore } from '@Core/Stores/sidebar';

import Button from '@Core/Components/Form/Button.vue';

const toast = useToast();

const sideBarStore = useSideBarStore();

const props = defineProps({
  filters: Object,
  fetchHandler: Function,
  scrollHeight: {
    type: String,
    default: null,
  },
});

const attrs = useAttrs();

const filters = ref({ ...props.filters });

const loading = ref(true);

const initLazyParams = {};
if (attrs.sortField) {
  initLazyParams.sortField = attrs.sortField;
}

if (attrs.sortOrder) {
  initLazyParams.sortOrder = attrs.sortOrder;
}

const lazyParams = ref(initLazyParams);
const records = ref([]);
const metadataInitial = {
  total: 0,
  from: 1,
  to: 1,
  current_page: 1,
  last_page: 1,
  per_page: 10,
};
const metadata = ref(metadataInitial);

const loadLazyData = async () => {
  loading.value = true;
  lazyParams.value = { ...lazyParams.value };

  const response = await props.fetchHandler(lazyParams.value);

  if (!response) {
    toast.add({
      severity: 'error',
      summary: trans('generics.tables.errors.could_not_load_the_data_summary'),
      detail: trans('generics.tables.errors.could_not_load_the_data'),
    });

    records.value = [];
    metadata.value = metadataInitial;
    loading.value = false;
    return;
  }

  records.value = response.data;
  metadata.value = response;
  loading.value = false;
};

defineExpose({ loadLazyData, records, metadata });

const onPage = (event) => {
  lazyParams.value = event;
  loadLazyData();
};

const onSort = (event) => {
  lazyParams.value = event;
  loadLazyData();
};

const onFilter = () => {
  lazyParams.value.filters = filters.value;
  loadLazyData();
};

const clearFilter = () => {
  lazyParams.value.filters = {};
  loadLazyData();
};

onMounted(() => {
  loadLazyData();
});
</script>

<template>
  <div
    class="overflow-x-auto"
    :class="{ 'sm:w-[calc(100vw-65px)] md:w-full ': sideBarStore.show, 'sm:w-[calc(100vw-65px)] md:w-[calc(100vw-65px)] lg:w-[calc(100vw-380px)]': !sideBarStore.show }"
  >
    <DataTable
      class="w-full"
      lazy
      dataKey="id"
      filterDisplay="menu"
      paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
      v-model:filters="filters"
      scrollable
      :scrollHeight="props.scrollHeight"
      tableStyle="min-width: 100px"
      v-bind="attrs"
      :stripedRows="true"
      :value="records"
      :totalRecords="metadata.total"
      :first="metadata.from"
      :last="metadata.to"
      :currentPage="metadata.current_page"
      :totalPages="metadata.last_page"
      :rows="metadata.per_page"
      :paginator="metadata.total > 0"
      :filters="filters"
      :rowsPerPageOptions="[5, 10, 25, 50, 100]"
      :loading="loading"
      :currentPageReportTemplate="metadata.total > 0 ? __('generics.tables.pagination.template', {
        from: metadata.from || 1,
        to: metadata.to || 1,
        total: metadata.total || 1
      }) : ''"
      @page="onPage($event)"
      @sort="onSort($event)"
      @filter="onFilter($event)"
    >
      <template #header>
        <div class="flex justify-between">
          <div class="sm:hidden md:block">
            <Button
              type="button"
              icon="pi pi-filter-slash"
              label="Limpiar"
              @click="clearFilter"
            />
          </div>
          <IconField>
            <InputIcon>
              <i class="pi pi-search" />
            </InputIcon>
            <InputText v-model="filters['global'].value" :placeholder="__('generics.tables.search') + '...'" @keyup.enter="onFilter" />
          </IconField>
        </div>
      </template>

      <template #empty> {{ __('generics.tables.empty') }} </template>

      <slot></slot>
    </DataTable>
  </div>
</template>
