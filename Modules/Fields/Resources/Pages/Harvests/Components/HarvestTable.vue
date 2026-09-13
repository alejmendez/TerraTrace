<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import Toast from 'primevue/toast';

import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import Column from 'primevue/column';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';

import CardSection from '@Core/Components/CardSection.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';

import Datatable from '@Core/Components/Table/Datatable.vue';
import HarvestService from '@Fields/Services/HarvestService.js';
import { defaultDeleteHandler } from '@Core/Utils/table.js';

import { can } from '@Auth/Services/Auth';
import { stringToFormat } from '@Core/Utils/date';

const toast = useToast();
const confirm = useConfirm();

const props = defineProps({
  field_id: {
    type: Number,
  },
  quarter_id: {
    type: Number,
  },
  show_actions: {
    type: Boolean,
    default: true,
  },
  harvest_available_years: Array,
  harvest_available_weeks: Array,
  fields: Array,
  quarters: Array,
  users: Array,
});

const datatable = ref(null);

const filter_year_options = [{ value: null, text: 'Todos' }, ...props.harvest_available_years];
const harvest_available_weeks = props.harvest_available_weeks;
const filter_field_options = props.fields;
const filter_quarter_options = props.quarters;
const filter_user_options = props.users;

const filters = {
  global: { value: null, matchMode: FilterMatchMode.CONTAINS },
  year: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
  week: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.EQUALS }] },
  batch: { operator: FilterOperator.AND, constraints: [{ value: null, matchMode: FilterMatchMode.CONTAINS }] },
  'details.quarter.field_id': { value: null, matchMode: FilterMatchMode.EQUALS },
  'details.quarter_id': { value: null, matchMode: FilterMatchMode.EQUALS },
  'farmer.id': { value: null, matchMode: FilterMatchMode.EQUALS },
};

const form = reactive({
  year: filter_year_options[0],
  field: [],
  last_field: [],
  quarter: [],
});

const canShow = can('harvests.show');
const canEdit = can('harvests.edit');
const canDestroy = can('harvests.destroy');

const fetchHandler = async (params) => {
  const year = form.year.value;
  if (!params.filters) {
    params.filters = {};
  }

  if (year) {
    if (params.filters?.year) {
      params = JSON.parse(JSON.stringify(params));
      params.filters.year.constraints.push({ value: year, matchMode: FilterMatchMode.EQUALS });
    } else {
      params.filters.year = {
        operator: FilterOperator.AND,
        constraints: [{ value: year, matchMode: FilterMatchMode.EQUALS }],
      };
    }
  }

  if (props.field_id) {
    params.filters['details.quarter.field_id'] = {
      value: { value: props.field_id },
      matchMode: FilterMatchMode.EQUALS,
    };
  }

  if (props.quarter_id) {
    params.filters['details.quarter_id'] = {
      value: { value: props.quarter_id },
      matchMode: FilterMatchMode.EQUALS,
    };
  }

  return await HarvestService.list({ ...params });
};

const filterHandler = async () => {
  datatable.value.loadLazyData();
};

const deleteHandler = (record) => {
  defaultDeleteHandler(confirm, datatable, toast, () => HarvestService.del(record.id));
};

const weightTotal = computed(() => {
  let total = datatable.value?.metadata?.details_sum_weight / 1000;

  if (isNaN(total)) {
    total = 0;
  }

  return `${number_format(total)} Kgs`;
});

const unitCountTotal = computed(() => {
  let total = datatable.value?.metadata?.details_count;
  if (isNaN(total)) {
    total = 0;
  }

  return number_format(total);
});

const number_format = (n) => {
  return new Intl.NumberFormat('es-CL', { maximumFractionDigits: 2 }).format(n);
};
</script>

<template>
  <Toast />

  <div class="grid md:grid-cols-3 sm:grid-cols-1 gap-4 items-stretch mb-4">
    <CardSection sectionClass="flex-1 mt-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5" wrapperClass="p-5">
      <div class="text-gray-400 pb-1">{{ __('harvest.table_filters.year') }}</div>
      <VSelect
        id="year"
        v-model="form.year"
        :placeholder="__('generics.please_select')"
        :options="filter_year_options"
        @change="filterHandler"
      />
    </CardSection>

    <CardSection sectionClass="flex-1 mt-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5" wrapperClass="p-5">
      <div class="text-gray-400 pb-1">Unidades</div>
      <div class="pb-3 text-3xl font-bold dark:text-gray-100">{{ unitCountTotal }}</div>
    </CardSection>


    <CardSection sectionClass="flex-1 mt-5 rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5" wrapperClass="p-5">
      <div class="text-gray-400 pb-1">Peso Total</div>
      <div class="pb-3 text-3xl font-bold dark:text-gray-100">{{ weightTotal }}</div>
    </CardSection>
  </div>

  <Datatable
    ref="datatable"
    :filters="filters"
    :fetchHandler="fetchHandler"
    sortField="date"
    :sortOrder="1"
  >
    <Column field="week" :header="__('harvest.table.week')" :showFilterMatchModes="false" sortable frozen style="min-width: 200px">
      <template #body="{ data }">
        {{ __('harvest.table_data.date', { week: data.week, year: data.year }) }}
      </template>
      <template #filter="{ filterModel }">
        <Select v-model="filterModel.value" :options="harvest_available_weeks" optionLabel="text" placeholder="Todos" />
      </template>
    </Column>

    <Column field="date" :header="__('harvest.table.date')" :showFilterMatchModes="false" sortable style="min-width: 200px">
      <template #body="{ data }">
        {{ stringToFormat(data.date) }}
      </template>
      <template #filter="{ filterModel }">
        <Select v-model="filterModel.value" :options="harvest_available_weeks" optionLabel="text" placeholder="Todos" />
      </template>
    </Column>

    <Column field="batch" :header="__('harvest.table.batch')" sortable style="min-width: 200px">
      <template #body="{ data }">
        {{ data.batch }}
      </template>
      <template #filter="{ filterModel }">
        <InputText v-model="filterModel.value" type="text" placeholder="Buscar por batch" />
      </template>
    </Column>

    <Column
      field="field.names"
      filterField="details.quarter.field_id"
      :showFilterMatchModes="false"
      :header="__('harvest.table.field')"
      sortable
      style="min-width: 200px"
      v-if="props.field_id === undefined && props.quarter_id === undefined"
    >
      <template #body="{ data }">
        {{ data.field_names }}
      </template>
      <template #filter="{ filterModel }">
        <Select v-model="filterModel.value" :options="filter_field_options" optionLabel="text" placeholder="Todos" />
      </template>
    </Column>

    <Column
      field="quarter.name"
      filterField="details.quarter_id"
      :showFilterMatchModes="false"
      :header="__('harvest.table.quarter')"
      sortable
      style="min-width: 200px"
      v-if="props.quarter_id === undefined"
    >
      <template #body="{ data }">
        {{ data.quarter_names }}
      </template>
      <template #filter="{ filterModel }">
        <Select v-model="filterModel.value" :options="filter_quarter_options" optionLabel="text" placeholder="Todos" />
      </template>
    </Column>

    <Column field="total_weight" :header="__('harvest.table.weight')" sortable style="min-width: 200px">
      <template #body="{ data }">
        {{ number_format(data.total_weight / 1000) }} Kgs
      </template>
    </Column>

    <Column field="unit_count" :header="__('harvest.table.count_details')" sortable style="min-width: 200px">
      <template #body="{ data }">
        {{ number_format(data.unit_count) }}
      </template>
    </Column>

    <Column field="farmer.name" filterField="farmer.id" :showFilterMatchModes="false" :header="__('harvest.table.responsible')" sortable style="min-width: 200px">
      <template #body="{ data }">
        {{ data.farmer_name }}
      </template>
      <template #filter="{ filterModel }">
        <Select v-model="filterModel.value" :options="filter_user_options" optionLabel="text" placeholder="Todos" />
      </template>
    </Column>

    <Column :exportable="false" style="min-width: 130px" v-if="props.show_actions">
      <template #body="slotProps">
        <Link :href="route('harvests.show', slotProps.data.id)" v-if="canShow">
          <span class="material-symbols-rounded cursor-pointer transition-all text-slate-500 hover:text-sky-600">visibility</span>
        </Link>
        <Link :href="route('harvests.edit', slotProps.data.id)" v-if="canEdit">
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

    <ColumnGroup type="footer">
      <Row>
        <Column footer="Totals:" :colspan="props.quarter_id ? 3 : (props.field_id ? 4 : 5)" footerStyle="text-align:right"/>
        <Column :footer="weightTotal" />
        <Column :footer="unitCountTotal" :colspan="3" />
      </Row>
    </ColumnGroup>
  </Datatable>
</template>
