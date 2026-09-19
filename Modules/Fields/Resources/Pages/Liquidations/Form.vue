<script setup>
import { ref, computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import { format, getWeek, endOfWeek, startOfWeek } from 'date-fns';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionDialog from '@Core/Components/Collection/CollectionDialog.vue';
import CollectionFieldWrapper from '@Core/Components/Collection/CollectionFieldWrapper.vue';
import AddImporter from '@Core/Components/Form/AddImporter.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';

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
    <CollectionCardSection>
      <CollectionFieldWrapper :label="__('liquidation.form.date.label')" :message="form.errors.date">
        <div class="flex items-stretch gap-2">
          <CollectionInput
            v-model="date_rendered"
            class="grow"
            @click="handler_open_datepicker"
          />
          <CollectionButton severity="secondary" icon="calendar_month" @click="handler_open_datepicker" />
        </div>
      </CollectionFieldWrapper>

      <div class="grid grid-cols-12">
        <CollectionSelect
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

      <CollectionInput
        id="delivery_date"
        type="date"
        v-model="form.delivery_date"
        :label="__('liquidation.form.delivery_date.label')"
        :message="form.errors.delivery_date"
        :maxDate="new Date()"
      />

      <CollectionInput
        id="reception_date"
        type="date"
        v-model="form.reception_date"
        :label="__('liquidation.form.reception_date.label')"
        :message="form.errors.reception_date"
        :maxDate="new Date()"
      />

      <CollectionSelect
        id="field_id"
        v-model="form.field_id"
        :placeholder="__('generics.please_select')"
        :options="props.fields"
        :label="__('liquidation.form.field_id.label')"
        :message="form.errors.field_id"
      />
    </CollectionCardSection>
    <CollectionCardSection>
      <CollectionFieldWrapper :label="__('liquidation.form.weight_with_earth.label')" :message="form.errors.weight_with_earth">
        <CollectionInput
          v-model="form.weight_with_earth"
          type="number"
          :min="0"
          :max="200000"
          :step="0.1"
          sufix="kg"
        />
      </CollectionFieldWrapper>
      <CollectionFieldWrapper :label="__('liquidation.form.weight_washed.label')" :message="form.errors.weight_washed">
        <CollectionInput
          v-model="form.weight_washed"
          type="number"
          :min="0"
          :max="200000"
          :step="0.1"
          sufix="kg"
        />
      </CollectionFieldWrapper>
      <CollectionFieldWrapper :label="__('liquidation.form.dollar_value.label')" :message="form.errors.dollar_value">
        <CollectionInput
          v-model="form.dollar_value"
          type="number"
          :min="0"
          :max="200000"
          :step="0.1"
          sufix="$"
        />
      </CollectionFieldWrapper>
    </CollectionCardSection>
    <CollectionCardSection :headerText="__('liquidation.sections.commercial_categories')" wrapperClass="p-4 grid md:grid-cols-10 sm:grid-cols-1 gap-x-2 gap-y-1">
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
          <CollectionInput
            v-model="form.products[commercial.id].price"
            :message="form.errors[`products.${index}.price`]"
            type="number"
            :min="0"
            :max="200000"
            :step="0.1"
          />
        </div>
        <div class="col-span-3">
          <CollectionInput
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
    </CollectionCardSection>
    <CollectionCardSection :headerText="__('liquidation.sections.rejected_categories')" wrapperClass="p-4 grid md:grid-cols-10 sm:grid-cols-1 gap-x-2 gap-y-1">
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
          <CollectionInput
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
    </CollectionCardSection>
  </form>

  <CollectionDialog v-model:visible="show_modal_datepicker" title="Seleccionar Fecha">
    <CollectionInput
      id="date_picker"
      v-model="form.date"
      type="date"
      @change="handler_date_selected"
    />
  </CollectionDialog>
</template>
