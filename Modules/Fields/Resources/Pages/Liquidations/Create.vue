<script setup>
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import FormLiquidation from '@Fields/Pages/Liquidations/Form.vue';

const props = defineProps({
  importers: Array,
  category_products: Array,
  fields: Array,
});

const form = useForm({
  date: null,
  delivery_date: null,
  reception_date: null,
  importer_id: [],
  weight_with_earth: 0,
  weight_washed: 0,
  dollar_value: 0,
  field_id: [],
  products: props.category_products.reduce((a, v) => ({ ...a, [v.id]: { ...v, category_product_id: v.id, price: 0, weight: 0 } }), {}),
});

const submitHandler = () => form.post(route('liquidations.store'));
</script>

<template>
  <AuthenticatedLayout :title="__('liquidation.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('liquidation.titles.create')"
      :breadcrumbs="[{ to: 'liquidations.index', text: __('liquidation.titles.entity_breadcrumb') }, { text: __('generics.actions.create') }]"
      :form="{ instance: form, submitHandler, submitText: __('generics.buttons.create'), hrefCancel: route('liquidations.index') }"
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
