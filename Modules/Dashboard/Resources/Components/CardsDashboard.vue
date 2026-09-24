<script setup>
import { router } from '@inertiajs/vue3';
import { formatNumber } from '@Core/Utils/format';

import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';

const props = defineProps({
  field: Object,
  harvest_data: Object,
  task_data: Object,
});

const task_data = props.task_data;

const percent_pending_tasks = getPorcent(task_data.tasks_totals, task_data.pending_tasks);
const percent_tasks_in_progress = getPorcent(task_data.tasks_totals, task_data.tasks_in_progress);

const navigateToTasks = (queryParams) => {
  router.get(route('tasks.index') + '?' + queryParams);
};

function getPorcent(total, num) {
  if (total === 0) {
    return 100;
  }

  return ((num * 100) / total).toFixed(2);
}
</script>

<template>
  <section class="grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 gap-4">
    <div class="terra-metric-card mt-5 p-5 rounded-xl card-section">
      <div class="terra-metric-icon terra-metric-icon--green"><CollectionIcon name="inventory_2" :size="22" /></div>
      <div>
        <div class="text-sm text-[#5e7369] font-bold">Temporada {{ harvest_data.years_variation[0] }}</div>
        <div class="text-2xl font-extrabold mb-1">{{ formatNumber(harvest_data.total_weight_of_last_harvest) }} kgs</div>
        <div class="text-xs text-[#687b72]">
          Promedio: {{ formatNumber(harvest_data.average_weight_per_plant) }} gr por planta
        </div>
      </div>
    </div>

    <div class="terra-metric-card mt-5 p-5 rounded-xl card-section">
      <div class="terra-metric-icon terra-metric-icon--amber"><CollectionIcon name="trending_up" :size="22" /></div>
      <div>
        <div class="text-sm text-[#5e7369] font-bold">Variación de cosecha</div>
        <div class="text-2xl font-extrabold mb-1">{{ formatNumber(harvest_data.variation_between_harvests) }} %</div>
        <div
          class="flex items-center justify-normal text-xs font-bold"
          :class="{
            'text-green-700': harvest_data.variation_between_harvests >= 0,
            'text-red-600': harvest_data.variation_between_harvests < 0,
          }"
        >
          <span>{{ harvest_data.variation_between_harvests >= 0 ? 'Incremento' : 'Disminución' }}</span>
          <CollectionIcon name="straight" :size="16" class="align-middle" :class="{ 'rotate-180' : harvest_data.variation_between_harvests < 0 }" />
        </div>
      </div>
    </div>

    <div
      class="terra-metric-card mt-5 p-5 rounded-xl card-section cursor-pointer"
      @click="navigateToTasks('status=overdued')"
    >
      <div class="terra-metric-icon terra-metric-icon--red"><CollectionIcon name="assignment_late" :size="22" /></div>
      <div class="grow">
        <div class="text-sm text-[#5e7369] font-bold">Tareas atrasadas</div>
        <div class="text-2xl font-extrabold">{{ task_data.pending_tasks }}</div>
        <div class="flex justify-between mt-2 text-xs text-[#687b72]">
          <div>{{ task_data.tasks_totals }} tareas</div>
          <div>{{ formatNumber(percent_pending_tasks) }}%</div>
        </div>
        <div class="h-1.5 bg-[#f4e4df] mt-2 rounded-full">
          <div class="bg-[#c94c32] h-full rounded-full" :style="`width: ${percent_pending_tasks}%;`"></div>
        </div>
      </div>
    </div>

    <div
      class="terra-metric-card mt-5 p-5 rounded-xl card-section cursor-pointer"
      @click="navigateToTasks('status=started,overdued')"
    >
      <div class="terra-metric-icon terra-metric-icon--blue"><CollectionIcon name="pending_actions" :size="22" /></div>
      <div class="grow">
        <div class="text-sm text-[#5e7369] font-bold">Tareas en curso</div>
        <div class="text-2xl font-extrabold">{{ task_data.tasks_in_progress }}</div>
        <div class="flex justify-between mt-2 text-xs text-[#687b72]">
          <div>{{ task_data.tasks_totals }} tareas</div>
          <div>{{ formatNumber(percent_tasks_in_progress) }}%</div>
        </div>
        <div class="h-1.5 bg-[#dcebf7] mt-2 rounded-full">
          <div class="bg-[#3685c7] h-full rounded-full" :style="`width: ${percent_tasks_in_progress}%;`"></div>
        </div>
      </div>
    </div>
  </section>
</template>
