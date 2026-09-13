<script setup>
import { ref, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { format, getWeek, endOfWeek, startOfWeek } from 'date-fns';

import Dialog from 'primevue/dialog';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import InputGroup from 'primevue/inputgroup';
import InputGroupAddon from 'primevue/inputgroupaddon';

import CardSection from '@Core/Components/CardSection.vue';
import VElementFormWrapper from '@Core/Components/Form/VElementFormWrapper.vue';
import AddImporter from '@Core/Components/Form/AddImporter.vue';
import VInput from '@Core/Components/Form/VInput.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';
import Button from '@Core/Components/Form/Button.vue';

const props = defineProps({
  form: Object,
  importers: Array,
  category_products: Array,
  submitHandler: Function,
  fields: Array,
});

const form = props.form;
const importers = ref(props.importers);

const categories_commercial = props.category_products.filter((a) => a.is_commercial);
const categories_not_commercial = props.category_products.filter((a) => !a.is_commercial);

const dateRenderText = (m) => {
  const start = format(startOfWeek(m, { weekStartsOn: 1 }), 'dd/MM/yyyy');
  const end = format(endOfWeek(m, { weekStartsOn: 1 }), 'dd/MM/yyyy');
  const week = getWeek(m, { weekStartsOn: 1 });
  return trans('harvest.form.date.renderText', { week, start, end });
};

const date_rendered = ref(form.date ? dateRenderText(form.date) : '');
const show_modal_datepicker = ref(false);

const handler_open_datepicker = () => {
  show_modal_datepicker.value = true;
};

const handler_date_selected = () => {
  date_rendered.value = dateRenderText(form.date);
  show_modal_datepicker.value = false;
};

const addImporterCallback = (newType) => {
  importers.value = [...importers.value, { value: newType.id, text: newType.name }];
};

const total_categories_commercial = computed(() => {
  const products = Object.values(form.products);
  const productsFiltered = products.filter((a) => props.category_products.find((b) => b.id === a.category_product_id).is_commercial);
  const sum = productsFiltered.reduce((acc, curr) => acc + parseFloat(curr.weight), 0);
  const sumRounded = Math.round(sum * 100) / 100;
  return `${sumRounded} Kg`;
});

const total_categories_not_commercial = computed(() => {
  const products = Object.values(form.products);
  const productsFiltered = products.filter((a) => !props.category_products.find((b) => b.id === a.category_product_id).is_commercial);
  const sum = productsFiltered.reduce((acc, curr) => acc + parseFloat(curr.weight), 0);
  const sumRounded = Math.round(sum * 100) / 100;
  return `${sumRounded} Kg`;
});
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CardSection>
      <VElementFormWrapper :label="__('liquidation.form.date.label')" :message="form.errors.date">
        <InputGroup>
          <InputText
            fluid
            v-model="date_rendered"
            variant="filled"
            @click="handler_open_datepicker"
          />
          <Button severity="secondary" icon="pi pi-calendar" @click="handler_open_datepicker" />
        </InputGroup>
      </VElementFormWrapper>

      <div class="grid grid-cols-12">
        <VSelect
          id="importer_id"
          v-model="form.importer_id"
          classWrapper="col-span-11"
          :placeholder="__('generics.please_select')"
          :options="importers"
          :label="__('liquidation.form.importer_id.label')"
          :message="form.errors.importer_id"
        />
        <div class="ms-2" style="margin-top: 28px;">
          <AddImporter :callback="addImporterCallback" />
        </div>
      </div>

      <VInput
        id="delivery_date"
        type="date"
        v-model="form.delivery_date"
        :label="__('liquidation.form.delivery_date.label')"
        :message="form.errors.delivery_date"
        :maxDate="new Date()"
      />

      <VInput
        id="reception_date"
        type="date"
        v-model="form.reception_date"
        :label="__('liquidation.form.reception_date.label')"
        :message="form.errors.reception_date"
        :maxDate="new Date()"
      />

      <VSelect
        id="field_id"
        v-model="form.field_id"
        :placeholder="__('generics.please_select')"
        :options="props.fields"
        :label="__('liquidation.form.field_id.label')"
        :message="form.errors.field_id"
      />
    </CardSection>
    <CardSection>
      <VElementFormWrapper :label="__('liquidation.form.weight_with_earth.label')" :message="form.errors.weight_with_earth">
        <InputGroup>
          <InputText
            fluid
            v-model="form.weight_with_earth"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
          <InputGroupAddon>{{ __('liquidation.weight_unit') }}</InputGroupAddon>
        </InputGroup>
      </VElementFormWrapper>
      <VElementFormWrapper :label="__('liquidation.form.weight_washed.label')" :message="form.errors.weight_washed">
        <InputGroup>
          <InputText
            fluid
            v-model="form.weight_washed"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
          <InputGroupAddon>{{ __('liquidation.weight_unit') }}</InputGroupAddon>
        </InputGroup>
      </VElementFormWrapper>
      <VElementFormWrapper :label="__('liquidation.form.dollar_value.label')" :message="form.errors.dollar_value">
        <InputGroup>
          <InputText
            fluid
            v-model="form.dollar_value"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
          <InputGroupAddon>{{ __('liquidation.currency_unit') }}</InputGroupAddon>
        </InputGroup>
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
          {{ form.products[commercial.id].name }}
        </div>
        <div class="col-span-3">
          <VInput
            v-model="form.products[commercial.id].price"
            :message="form.errors[`products.${index}.price`]"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
        </div>
        <div class="col-span-3">
          <VInput
            v-model="form.products[commercial.id].weight"
            :message="form.errors[`products.${index}.weight`]"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
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
          {{ form.products[commercial.id].name }}
        </div>
        <div class="col-span-3">
          <VInput
            v-model="form.products[commercial.id].weight"
            :message="form.errors[`products.${index}.weight`]"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
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
  </form>

  <Dialog v-model:visible="show_modal_datepicker" modal header="Seleccionar Fecha">
    <DatePicker v-model="form.date" fluid inline showWeek @date-select="handler_date_selected" />
  </Dialog>
</template>
