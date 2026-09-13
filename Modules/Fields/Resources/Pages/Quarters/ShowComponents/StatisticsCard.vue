<script setup>
import { ref, toRaw, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import ProgressSpinner from 'primevue/progressspinner';

import CardSection from '@Core/Components/CardSection.vue';
import VSelect from '@Core/Components/Form/VSelect.vue';
import Button from '@Core/Components/Form/Button.vue';
import VInput from '@Core/Components/Form/VInput.vue';

import QuarterService from '@Fields/Services/QuarterService.js';
import { can } from '@Auth/Services/Auth';

const props = defineProps({
  quarter: Object,
  scale_types: Array,
});

const plants = ref([]);
const tableCols = ref([]);
const dataPlantsPosition = ref([]);
const dataHarvests = ref([]);
const open = ref(false);
const loading = ref(true);
const distributionPlants = ref('');
const current_plant = ref({});
const detail_current_plant = ref({});

const scaleTypes = ref(props.scale_types);
const scaleType = ref(scaleTypes.value[0]);

// TODO: Se oculta temporalmente, buscar alguna alternativa para que no se pueda editar la distribución de plantas
// se puede agregar en la seccion del engranaje
const canUpdatePlantsPosition = false; //can('quarters.plants.update.position');

const processDistribution = (distribution) =>
  distribution
    .split('\n')
    .filter((row) => row.trim())
    .map((row) => row.split('\t'));

const changeDistribution = () => {
  const distribution = processDistribution(distributionPlants.value);
  const data = Object.fromEntries(toRaw(dataPlantsPosition.value).map((item) => [item.code, item]));

  const distributionData = [];
  const dataToSave = [];

  for (let i = 0; i < distribution.length; i++) {
    const row = distribution[i];
    if (row.length !== distribution[0].length) {
      console.error('Invalid row length');
      return;
    }

    const rowData = row.map((ele, j) => {
      const plant = data[ele];
      if (plant) dataToSave.push([ele, i, j]);
      return plant || '';
    });

    distributionData.push(rowData);
  }

  QuarterService.updatePlantsPosition(props.quarter.id, dataToSave);
  plants.value = distributionData;
};

const generatePlantsDispositionWithPosition = (dataPlants) => {
  const plantsByPosition = Object.fromEntries(dataPlants.filter((p) => p.position).map((p) => [p.position, p]));
  const plantsByCols = Object.groupBy(dataPlants, (p) => p.row);
  const cols = Object.keys(plantsByCols);

  const maxRows =
    Math.max(
      ...dataPlants.map((p) => parseInt(p.position?.split(',')[0], 10) || 0),
      Object.values(plantsByCols).reduce((max, col) => Math.max(max, col.length), 0),
    ) + 1;

  const data = Array.from({ length: maxRows }, (_, i) =>
    cols.map((_, j) => {
      const pos = `${i},${j}`;
      const plant = plantsByPosition[pos];
      if (plant) {
        plant.number = plant.code;
        const [_, position] = plant.code.match(/\d+/g);
        plant.position = position;
        plant.colorByWeight = generarColorPorPorcentaje(plant.scaleByWeight);
        plant.colorByQuantity = generarColorPorPorcentaje(plant.scaleByQuantity);
      }
      return plant || null;
    }),
  );

  tableCols.value = cols;
  plants.value = data;
};

const generarColorPorPorcentaje = (porcentaje, colorFin = '#008FFB') => {
  const colorInicio = { r: 246, g: 246, b: 246 };
  const hexToRgb = (hex) => {
    const bigint = parseInt(hex.slice(1), 16);
    return { r: (bigint >> 16) & 246, g: (bigint >> 8) & 246, b: bigint & 246 };
  };

  const colorFinal = hexToRgb(colorFin);
  const factor = Math.max(0, Math.min(100, porcentaje)) / 100;
  const interpolate = (start, end) => Math.round(start + (end - start) * factor);

  const r = interpolate(colorInicio.r, colorFinal.r);
  const g = interpolate(colorInicio.g, colorFinal.g);
  const b = interpolate(colorInicio.b, colorFinal.b);

  return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
};

const getHarvestDetailFromPlant = (plant) => {
  return plant?.data ? Object.values(plant.data).flat() : [];
};

const setCurrentPlant = (plant) => {
  current_plant.value = plant?.code ? plant : current_plant.value;
  detail_current_plant.value = getHarvestDetailFromPlant(current_plant.value);
};

onMounted(async () => {
  const { plants: plantData, harvests } = await QuarterService.getPlants(props.quarter.id);
  dataPlantsPosition.value = plantData;
  dataHarvests.value = harvests;
  generatePlantsDispositionWithPosition(plantData);

  loading.value = false;
});
</script>

<style scoped>
table {
  text-align: center;
  border: solid 10px var(--p-neutral-300);
}
table thead tr th {
  background-color: var(--p-zinc-300);
  padding: 0;
  font-weight: normal;
  font-size: 10px;
}

table tbody tr td {
  height: 15px;
  width: 35px;
  line-height: 0.5;
  background-color: var(--p-neutral-300);
  cursor: pointer;
  border-left: solid 1px #ddd;
}

table tbody tr td.border_cell {
  border: solid 1px #ccc;
}

table tbody tr td.border_cell_left {
  border-left: solid 1px #ccc;
}

.dark table {
  border: solid 2px var(--p-gray-800);
}
.dark table thead tr th {
  background-color: var(--p-gray-500);
}

.dark table tbody tr td {
  background-color: var(--p-gray-500);
}

.dark table tbody tr td.border_cell {
  border: solid 1px #ccc;
}

.dark table tbody tr td.border_cell_left {
  border-left: solid 1px #ccc;
}
</style>

<template>
  <CardSection :header-text="__('field.show.statistics.title')" wrapperClass="p-5 grid gap-4">
    <div v-if="canUpdatePlantsPosition">
      <Button
        :label="__('harvest.buttons.change_distribution')"
        @click.prevent="open = true"
      />
      <Dialog v-model:visible="open" modal header="cambiar distribucion de arboles" :style="{ maxWidth: '500px' }">
        <div class="grid gap-4 py-4">
          <div class="grid items-center gap-4">
            <VInput id="distributionPlants" type="textarea" class="min-h-36" v-model="distributionPlants" />
          </div>
        </div>
        <div class="flex justify-end">
          <Button type="submit" @click="changeDistribution" :label="__('generics.actions.create')" />
        </div>
      </Dialog>
    </div>

    <div class="py-6 grid md:grid-cols-2 gap-x-16 gap-y-4 sm:grid-cols-1">
      <VSelect
        id="scaleType"
        v-model="scaleType"
        :placeholder="__('generics.please_select')"
        :options="scaleTypes"
        :label="__('quarter.show.statistics.scale_type.label')"
      />
    </div>

    <div v-show="loading" class="text-center mt-5">
      <ProgressSpinner
        style="width: 50px; height: 50px"
        strokeWidth="8"
        fill="transparent"
        animationDuration=".5s"
        aria-label="Progress Spinner"
      />
    </div>

    <div v-show="!loading" class="grid lg:grid-cols-4 md:grid-cols-3 sm:grid-cols-1 gap-4">
      <table class="table-heat-map lg:col-span-3 md:col-span-2">
        <thead>
          <tr>
            <th v-for="col in tableCols" :key="col" v-text="col" />
          </tr>
        </thead>
        <tbody>
          <tr v-for="plantRow in plants" :key="plantRow">
            <td
              v-for="plant in plantRow"
              :key="plant?.code || 'empty'"
              v-html="'&nbsp;'"
              :class="{
                border_cell: plant?.code,
                border_cell_left: plant?.code,
              }"
              :style="{
                backgroundColor: scaleType === 'weight' ? plant?.colorByWeight : plant?.colorByQuantity,
              }"
              v-tooltip.top="plant?.code"
              @click="setCurrentPlant(plant)"
            />
          </tr>
        </tbody>
      </table>
      <div v-if="current_plant?.code">
        <div class="text-xl">{{ current_plant.code }}</div>
        <div>Total cosecha (grs).: {{ detail_current_plant.reduce((a, b) => a + b.weight, 0) }}</div>
        <div>Unidades: {{ detail_current_plant.length }}</div>
        <div>
          <Link :href="route('plants.show', current_plant.id) + '?current_tab=logs'">
            Ir a ficha de planta <span class="material-symbols-rounded">open_in_new</span>
          </Link>
        </div>
      </div>
    </div>
  </CardSection>
</template>
