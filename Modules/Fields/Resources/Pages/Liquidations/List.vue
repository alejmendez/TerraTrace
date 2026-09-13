<script setup>
import { ref, reactive, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import InputText from 'primevue/inputtext';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import CardSection from '@Core/Components/CardSection.vue';
import Datatable from '@Core/Components/Table/Datatable.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';

import LiquidationService from '@Fields/Services/LiquidationService.js.js';
import { stringToFormat } from '@Core/Utils/date';
import { defaultDeleteHandler } from '@Core/Utils/table.js';
import { can } from '@Auth/Services/Auth';
import { formatNumber } from '@Core/Utils/format';

const props = defineProps({
  toast: Object,
  importers: Array,
  liquidation_available_years: Array,
});

const toast = useToast();
const confirm = useConfirm();

const datatable = ref(null);
const filter_importer_options = ref(props.importers);
const year_value_default = { value: null, text: 'Todos' };
const filter_year_options = ref([year_value_default, ...props.liquidation_available_years]);

const form = reactive({
  year: year_value_default,
});

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  year: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
  week: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
  liquidation_number: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  delivery_date: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  importer_id: { value: null, matchMode: FilterMatchMode.EQUALS },
};

const canShow = can('liquidations.show');
const canEdit = can('liquidations.edit');
const canDestroy = can('liquidations.destroy');
const canCreate = can('liquidations.create');

const headerLinks = [];
if (canCreate) {
  headerLinks.push({ to: 'liquidations.create', text: 'generics.new' });
}

const fetchHandler = async (params) => {
  if (!params.rows) {
    params.rows = 25;
  }

  const year = form.year.value;
  if (year) {
    params.filters = {
      ...params.filters,
      year: {
        operator: FilterOperator.AND,
        constraints: [{ value: year, matchMode: FilterMatchMode.EQUALS }],
      },
    };
  } else if (params.filters?.year) {
    delete params.filters.year;
  }
  return await LiquidationService.list(params);
};

const deleteHandler = (record) => {
  defaultDeleteHandler(confirm, datatable, toast, () => TaskService.del(record.id));
};

const filterHandler = async () => {
  datatable.value.loadLazyData();
};

onMounted(async () => {
  if (props.toast) {
    toast.add(props.toast);
  }
});
</script>

<template>
  <AuthenticatedLayout :title="__('liquidation.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('liquidation.titles.entity_breadcrumb')"
      :breadcrumbs="[{ to: 'liquidations.index', text: __('liquidation.titles.entity_breadcrumb') }, { text: __('generics.list') }]"
      :links="headerLinks"
    />

    <CardSection wrapperClass="p-6 mb-5 grid md:grid-cols-3 gap-x-16 gap-y-4 sm:grid-cols-1">
      <div>
        <div class="text-gray-400 pb-1">{{ __('harvest.table_filters.year') }}</div>
        <VSelect
          id="year"
          v-model="form.year"
          :placeholder="__('generics.please_select')"
          :options="filter_year_options"
          @change="filterHandler"
        />
      </div>
    </CardSection>

    <Datatable
      ref="datatable"
      :filters="filters"
      :fetchHandler="fetchHandler"
      :sortOrder="1"
      scrollHeight="800px"
      sortField="week"
    >
      <Column field="week" :header="__('liquidation.table.week')" sortable frozen style="min-width: 200px">
        <template #body="{ data }">
          {{ data.week }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por semana" />
        </template>
      </Column>

      <Column field="delivery_date" :header="__('liquidation.table.delivery_date')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ stringToFormat(data.delivery_date) }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Fecha de Entrega" />
        </template>
      </Column>

      <Column field="importer_id" :header="__('liquidation.table.importer_id')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ data.importer.name }}
        </template>
        <template #filter="{ filterModel }">
          <InputText v-model="filterModel.value" type="text" placeholder="Buscar por Exportador" />
        </template>
      </Column>

      <Column field="total_commercial" :header="__('liquidation.table.total_commercial')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ formatNumber(data.total_commercial) }}
        </template>
      </Column>

      <Column field="total_not_commercial" :header="__('liquidation.table.total_not_commercial')" sortable style="min-width: 200px">
        <template #body="{ data }">
          {{ formatNumber(data.total_not_commercial) }}
        </template>
      </Column>

      <Column :exportable="false" style="min-width: 130px">
        <template #body="slotProps">
          <Link :href="route('liquidations.show', slotProps.data.id)" v-if="canShow">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
          </Link>
          <Link :href="route('liquidations.edit', slotProps.data.id)" v-if="canEdit">
            <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-emerald-600">edit</span>
          </Link>
          <span
            class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-pink-600"
            @click="deleteHandler(slotProps.data)"
            v-if="canDestroy"
          >
            delete
          </span>
        </template>
      </Column>
    </Datatable>
  </AuthenticatedLayout>
</template>
