<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormLiquidation from '@Fields/Pages/Liquidations/Form.vue';

import { stringToDate } from '@Core/Utils/date';

const props = defineProps({
  data: Object,
  importers: Array,
  category_products: Array,
  fields: Array,
});

const { data } = props.data;

const products = props.category_products.reduce((a, v) => {
  let product = data.products.find((a) => a.category_product_id === v.id) || {
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

const form = useForm({
  _method: 'PATCH',
  id: data.id,
  date: stringToDate(data.date),
  delivery_date: stringToDate(data.delivery_date),
  reception_date: stringToDate(data.reception_date),
  importer_id: data.importer_id,
  weight_with_earth: data.weight_with_earth,
  weight_washed: data.weight_washed,
  dollar_value: data.dollar_value,
  field_id: props.fields.find((a) => a.value == data.field_id.value),
  products,
});

const submitHandler = () => form.post(route('liquidations.update', data.id));
</script>

<template>
  <AuthenticatedLayout :title="__('liquidation.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('liquidation.titles.edit')"
      :breadcrumbs="[{ to: 'liquidations.index', text: __('liquidation.titles.entity_breadcrumb') }, { text: __('generics.actions.edit') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.save_edit'), hrefCancel: route('liquidations.index') }"
    />
    <FormLiquidation
      :form="form"
      :importers="importers"
      :category_products="category_products"
      :fields="fields"
      :submitHandler="submitHandler"
    />
  </AuthenticatedLayout>
</template>
