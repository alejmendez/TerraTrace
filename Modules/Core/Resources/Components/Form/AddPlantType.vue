<script setup>
import { ref } from 'vue';

import Dialog from 'primevue/dialog';
import VInput from '@Core/Components/Form/VInput.vue';
import Button from '@Core/Components/Form/Button.vue';

import plantTypeService from '@Fields/Services/PlantTypeService.js';

const props = defineProps({
  callback: Function,
});

const plant_type_name = ref('');
const open = ref(false);
const loading = ref(false);

const addPlantType = async () => {
  loading.value = true;
  const response = await plantTypeService.create({ name: plant_type_name.value });
  const newType = response.data.type;
  loading.value = false;
  open.value = false;
  props.callback(newType);
  plant_type_name.value = '';
};
</script>

<template>
  <Button
    severity="secondary"
    @click.prevent="open = true"
    icon="pi pi-plus"
  />
  <Dialog v-model:visible="open" modal header="Agregar un tipo de planta" :style="{ maxWidth: '425px' }">
    <div class="grid gap-4 py-4">
      <div class="grid items-center gap-4">
        <VInput
          id="plant_type_name"
          v-model="plant_type_name"
          :label="__('plant.form.plant_type_id.label')"
        />
      </div>
    </div>
    <div class="flex justify-end">
      <Button type="submit" @click="addPlantType" :label="__('generics.actions.create')" :loading="loading" />
    </div>
  </Dialog>
</template>
