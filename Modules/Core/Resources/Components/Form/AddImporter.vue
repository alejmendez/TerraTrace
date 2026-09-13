<script setup>
import { ref } from 'vue';

import Dialog from 'primevue/dialog';

import VInput from '@Core/Components/Form/VInput.vue';
import Button from '@Core/Components/Form/Button.vue';

import importerService from '@Fields/Services/ImporterService';

const props = defineProps({
  callback: Function,
});

const importer_name = ref('');
const open = ref(false);
const loading = ref(false);

const addImporter = async () => {
  loading.value = true;
  const response = await importerService.create({ name: importer_name.value });
  const newImporter = response.data.importer;
  loading.value = false;
  open.value = false;
  props.callback(newImporter);
  importer_name.value = '';
};
</script>

<template>
  <Button
    severity="secondary"
    @click.prevent="open = true"
    icon="pi pi-plus"
  />
  <Dialog v-model:visible="open" modal header="Agregar un exportador" :style="{ maxWidth: '425px' }">
    <div class="grid gap-4 py-4">
      <div class="grid items-center gap-4">
        <VInput
          id="importer_name"
          v-model="importer_name"
          :label="__('liquidation.form.importer_id.label')"
        />
      </div>
    </div>
    <div class="flex justify-end">
      <Button type="submit" @click="addImporter" :label="__('generics.actions.create')" :loading="loading" />
    </div>
  </Dialog>
</template>
