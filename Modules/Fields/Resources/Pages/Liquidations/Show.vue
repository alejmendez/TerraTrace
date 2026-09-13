<script setup>
import { ref, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

import { stringToDate, stringToFormat } from '@Core/Utils/date';
import { format, getWeek, endOfWeek, startOfWeek } from 'date-fns';
import { formatNumber } from '@Core/Utils/format';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import CardSection from '@Core/Components/CardSection.vue';
import VElementFormWrapper from '@Core/Components/Form/VElementFormWrapper.vue';

const props = defineProps({
  data: Object,
  importers: Array,
  category_products: Array,
  fields: Array,
});

const dataBase = props.data.data;

const products = props.category_products.reduce((a, v) => {
  let product = dataBase.products.find((a) => a.category_product_id === v.id) || {
    ...v,
    category_product_id: v.id,
    price: 0,
    weight: 0,
  };
  return {
    ...a,
    [v.id]: product,
  };
}, {});

const data = {
  date: stringToDate(dataBase.date),
  delivery_date: stringToFormat(dataBase.delivery_date),
  reception_date: stringToFormat(dataBase.reception_date),
  importer_id: dataBase.importer_id.text,
  weight_with_earth: dataBase.weight_with_earth,
  weight_washed: dataBase.weight_washed,
  dollar_value: dataBase.dollar_value,
  fields: dataBase.fields,
  products,
};

const categories_commercial = props.category_products.filter((a) => a.is_commercial);
const categories_not_commercial = props.category_products.filter((a) => !a.is_commercial);

const dateRenderText = (m) => {
  const start = format(startOfWeek(m, { weekStartsOn: 1 }), 'dd/MM/yyyy');
  const end = format(endOfWeek(m, { weekStartsOn: 1 }), 'dd/MM/yyyy');
  const week = getWeek(m, { weekStartsOn: 1 });
  return trans('harvest.form.date.renderText', { week, start, end });
};

const weekLiquidation = computed(() => getWeek(data.date, { weekStartsOn: 1 }));

const date_rendered = ref(data.date ? dateRenderText(data.date) : '');

const total_categories_commercial = computed(() => {
  const products = Object.values(data.products);
  const productsFiltered = products.filter((a) => props.category_products.find((b) => b.id === a.category_product_id).is_commercial);
  const sum = productsFiltered.reduce((acc, curr) => acc + parseFloat(curr.weight), 0);
  const sumRounded = Math.round(sum * 100) / 100;
  return `${formatNumber(sumRounded)} Kg`;
});

const total_categories_not_commercial = computed(() => {
  const products = Object.values(data.products);
  const productsFiltered = products.filter((a) => !props.category_products.find((b) => b.id === a.category_product_id).is_commercial);
  const sum = productsFiltered.reduce((acc, curr) => acc + parseFloat(curr.weight), 0);
  const sumRounded = Math.round(sum * 100) / 100;
  return `${formatNumber(sumRounded)} Kg`;
});
</script>

<template>
  <AuthenticatedLayout :title="__('liquidation.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('liquidation.titles.show', { week: weekLiquidation })"
      :breadcrumbs="[{ to: 'liquidations.index', text: __('liquidation.titles.entity_breadcrumb') }, { text: __('generics.actions.show') }]"
    />
    <CardSection>
      <VElementFormWrapper :label="__('liquidation.form.date.label')">
        <div>
          {{ date_rendered }}
        </div>
      </VElementFormWrapper>

      <VElementFormWrapper :label="__('liquidation.form.importer_id.label')">
        <div>
          {{ data.importer_id }}
        </div>
      </VElementFormWrapper>

      <VElementFormWrapper :label="__('liquidation.form.delivery_date.label')">
        <div>
          {{ data.delivery_date }}
        </div>
      </VElementFormWrapper>

      <VElementFormWrapper :label="__('liquidation.form.reception_date.label')">
        <div>
          {{ data.reception_date }}
        </div>
      </VElementFormWrapper>
    </CardSection>
    <CardSection>
      <VElementFormWrapper :label="__('liquidation.form.weight_with_earth.label')">
        <div>
          {{ formatNumber(data.weight_with_earth) }}
        </div>
      </VElementFormWrapper>
      <VElementFormWrapper :label="__('liquidation.form.weight_washed.label')">
        <div>
          {{ formatNumber(data.weight_washed) }}
        </div>
      </VElementFormWrapper>
      <VElementFormWrapper :label="__('liquidation.form.dollar_value.label')">
        <div>
          {{ formatNumber(data.dollar_value) }}
        </div>
      </VElementFormWrapper>
    </CardSection>
    <CardSection :headerText="__('liquidation.sections.commercial_categories')" wrapperClass="p-4 grid md:grid-cols-10 sm:grid-cols-1 gap-x-2 gap-y-1">
      <div class="col-span-4 font-semibold">
        {{ __('liquidation.form.commercial_categories.labels.name') }}
      </div>
      <div class="col-span-3 font-semibold">
        {{ __('liquidation.form.commercial_categories.labels.price') }}
      </div>
      <div class="col-span-3 font-semibold">
        {{ __('liquidation.form.commercial_categories.labels.weight') }}
      </div>
      <template v-for="(commercial, index) in categories_commercial">
        <div class="col-span-10 border-t mt-[2px]"></div>
        <div class="col-span-4 pt-3">
          {{ data.products[commercial.id].name }}
        </div>
        <div class="col-span-3">
          {{ formatNumber(data.products[commercial.id].price) }}
        </div>
        <div class="col-span-3">
          {{ formatNumber(data.products[commercial.id].weight) }}
        </div>
      </template>

      <div class="col-span-10 border-t mt-[2px]"></div>
      <div class="col-span-4 pt-3"></div>
      <div class="col-span-3 pt-3 text-right font-bold">
        {{ __('liquidation.total') }}:
      </div>
      <div class="col-span-3 pt-3 font-bold">
        {{ total_categories_commercial }}
      </div>
    </CardSection>
    <CardSection :headerText="__('liquidation.sections.rejected_categories')" wrapperClass="p-4 grid md:grid-cols-10 sm:grid-cols-1 gap-x-2 gap-y-1">
      <div class="col-span-7 font-semibold">
        {{ __('liquidation.form.commercial_categories.labels.name') }}
      </div>
      <div class="col-span-3 font-semibold">
        {{ __('liquidation.form.commercial_categories.labels.weight') }}
      </div>
      <template v-for="(commercial, index) in categories_not_commercial">
        <div class="col-span-10 border-t mt-[2px]"></div>
        <div class="col-span-7 pt-3">
          {{ data.products[commercial.id].name }}
        </div>
        <div class="col-span-3">
          {{ formatNumber(data.products[commercial.id].weight) }}
        </div>
      </template>
      <div class="col-span-10 border-t mt-[2px]"></div>
      <div class="col-span-7 pt-3 text-right font-bold">
        {{ __('liquidation.total') }}:
      </div>
      <div class="col-span-3 pt-3 font-bold">
        {{ total_categories_not_commercial }}
      </div>
    </CardSection>
  </AuthenticatedLayout>
</template>
