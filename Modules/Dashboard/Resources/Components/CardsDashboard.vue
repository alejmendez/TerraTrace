<script setup>
import { router } from '@inertiajs/vue3';
import { formatNumber } from '@Core/Utils/format';

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
    <div class="mt-5 p-5 rounded-xl card-section">
      <div class="text-gray-500 dark:text-gray-100 font-bold">Temporada {{ harvest_data.years_variation[0] }}:</div>
      <div class="text-3xl font-bold mb-2">{{ formatNumber(harvest_data.total_weight_of_last_harvest) }} kgs</div>
      <div class="text-sm">
        Promedio: {{ formatNumber(harvest_data.average_weight_per_plant) }} gr por planta
      </div>
    </div>

    <div class="mt-5 p-5 rounded-xl card-section">
      <div class="text-gray-500 dark:text-gray-100 font-bold">Cosecha {{ harvest_data.years_variation[1] }} - {{ harvest_data.years_variation[0] }}</div>
      <div class="text-3xl font-bold mb-2">{{ formatNumber(harvest_data.variation_between_harvests) }} %</div>
      <div
        class="text-sm text-green-500"
        :class="{
          'text-green-500': harvest_data.variation_between_harvests >= 0,
          'text-red-500': harvest_data.variation_between_harvests < 0,
        }"
      >
        {{ harvest_data.variation_between_harvests >= 0 ? 'Incremento' : 'Disminución'}}
        <span class="material-symbols-rounded !text-[18px]" :class="{ 'rotate-180' : harvest_data.variation_between_harvests < 0 }">straight</span>
      </div>
    </div>

    <div
      class="mt-5 p-5 rounded-xl bg-(--p-primary-500) shadow-sm text-gray-50 cursor-pointer"
      @click="navigateToTasks('status=overdued')"
    >
      <span class="text-3xl font-bold mr-2">{{ task_data.pending_tasks }}</span> Tareas Atrasadas
      <div class="flex justify-between mt-3">
        <div>{{ task_data.tasks_totals }} Tareas</div>
        <div>{{ formatNumber(percent_pending_tasks) }}%</div>
      </div>
      <div class="h-2 bg-(--p-primary-300) mt-2 rounded">
        <div class="bg-gray-50 h-full rounded" :style="`width: ${percent_pending_tasks}%;`"></div>
      </div>
    </div>

    <div
      class="mt-5 p-5 rounded-xl card-section cursor-pointer"
      @click="navigateToTasks('status=started,overdued')"
    >
      <span class="text-3xl font-bold mr-2">{{ task_data.tasks_in_progress }}</span> Tareas en curso
      <div class="flex justify-between mt-3">
        <div>{{ task_data.tasks_totals }} Tareas</div>
        <div>{{ formatNumber(percent_tasks_in_progress) }}%</div>
      </div>
      <div class="h-2 bg-(--p-primary-300) mt-2 rounded">
        <div class="bg-(--p-primary-500) h-full rounded" :style="`width: ${percent_tasks_in_progress}%;`"></div>
      </div>
    </div>
  </section>
</template>
