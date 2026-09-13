<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import HeaderCrud from '@Core/Components/Crud/HeaderCrud.vue';
import BulkWrapper from '@Core/Components/BulkWrapper.vue';

import VSelect from '@Core/Components/Form/VSelect.vue';
import VInputFile from '@Core/Components/Form/VInputFile.vue';

const props = defineProps({
  id: String,
  message_success: String,
  unprocessed_message: String,
  unprocessed_details: Array,
  error_message: String,
  import_errors: Array,
  harvests: Array,
  harvest_available_years: Array,
});

const harvest_available_years = ref(props.harvest_available_years);

const current_year = new Date().getFullYear();
const current_harvest = props.id ? props.harvests.find((h) => h.value == props.id) : null;

const current_year_option = current_harvest ? props.harvest_available_years.find((h) => h.value == current_harvest.year) : props.harvest_available_years.find((h) => h.value == current_year);
const harvests = ref(props.harvests.filter((h) => h.year == current_year_option.value));

const form = useForm({
  year: current_year_option,
  harvest_id: current_harvest,
  bulk_file: null,
});

const submitHandler = () => {
  if (form.harvest_id === '' || form.bulk_file === null) {
    return;
  }
  form.post(route('harvests.store.bulk'), {
    forceFormData: true,
  });
};

const changeFileHandler = (e) => {
  form.bulk_file = e.fileInput;
  submitHandler();
};

const handleYearChange = () => {
  form.harvest_id = null;
  harvests.value = props.harvests.filter((h) => h.year == form.year.value);
};
</script>

<template>
  <AuthenticatedLayout :title="__('harvest.titles.entity_breadcrumb')">
    <HeaderCrud
      :title="__('harvest.titles.bulk')"
      :breadcrumbs="[{ to: 'harvests.index', text: __('harvest.titles.entity_breadcrumb') }, { text: __('generics.actions.bulk') }]"
      :form="{ instance: form }"
    />
    <form @submit.prevent="submitHandler">
      <BulkWrapper
        :message_success="props.message_success"
        :unprocessed_message="props.unprocessed_message"
        :unprocessed_details="props.unprocessed_details"
        :error_message="props.error_message"
        :errors="props.import_errors"
        :title="__('generics.bulk.section_title')"
        downloadRoute="harvests.download.bulk.template"
      >
        <div class="px-6 pb-6 grid grid-cols-2 gap-x-16 gap-y-4">
          <VSelect
            v-model="form.year"
            :placeholder="__('generics.please_select')"
            :options="harvest_available_years"
            :label="__('harvest.bulk.form.year')"
            :message="form.errors.year"
            @change="handleYearChange"
          />

          <VSelect
            v-model="form.harvest_id"
            :placeholder="__('generics.please_select')"
            :options="harvests"
            :label="__('harvest.bulk.form.harvest_id')"
            :message="form.errors.harvest_id"
            @change="() => submitHandler()"
          />

          <div class="form-text col-span-2 form-text-type">
            <VInputFile
              :label="__('generics.form.file.select_a_file')"
              :withRemove="false"
              :showPathFile="true"
              accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
              @change="changeFileHandler"
            />
          </div>
        </div>
      </BulkWrapper>
    </form>
  </AuthenticatedLayout>
</template>
