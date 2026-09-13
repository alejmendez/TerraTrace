<script setup>
import { onMounted, ref } from 'vue';

import CardSection from '@Core/Components/CardSection.vue';

import { getGraph } from '@Fields/Services/Graphs';

const props = defineProps({
  plant: Object,
});

const plant = props.plant;

const type_graph = ref({ value: 'plant-on-demand-production', text: 'Cosecha por semana', type: 'area' });

const loading = ref(true);
const dataNotFound = ref(false);
const series = ref([]);

const chartOptions = {
  area: {
    chart: {
      type: 'area',
      zoom: {
        enabled: false,
      },
    },
    dataLabels: {
      enabled: false,
    },
    stroke: {
      curve: 'straight',
    },
    title: {
      text: '',
      align: 'left',
    },
    labels: [],
    xaxis: {
      type: 'string',
    },
    yaxis: {
      opposite: false,
    },
    legend: {
      horizontalAlign: 'left',
    },
  },
  pie: {
    chart: {
      type: 'pie',
      zoom: {
        enabled: false,
      },
    },
    title: {
      text: '',
      align: 'left',
    },
    labels: [],
    responsive: [
      {
        breakpoint: 480,
        options: {
          chart: {
            width: 200,
          },
          legend: {
            position: 'bottom',
          },
        },
      },
    ],
  },
  bar_percent: {
    chart: {
      type: 'bar',
      stacked: true,
      stackType: '100%',
      zoom: {
        enabled: false,
      },
    },
    plotOptions: {
      bar: {
        horizontal: false,
      },
    },
    stroke: {
      width: 1,
      colors: ['#fff'],
    },
    xaxis: {
      categories: [],
      convertedCatToNumeric: false,
    },
    fill: {
      opacity: 1,
    },
    legend: {
      position: 'top',
      horizontalAlign: 'left',
      offsetX: 40,
    },
    title: {
      text: '',
      align: 'left',
    },
  },
};

const chartOption = ref('area');

const filterHandler = async () => {
  loading.value = true;
  dataNotFound.value = false;
  const response = await getGraph(plant.id, type_graph.value.value);

  if (response.length === 0) {
    dataNotFound.value = true;
    loading.value = false;
    return;
  }

  const _chartOption = {
    ...chartOptions[type_graph.value.type],
    theme: {
      mode: localStorage.themeType,
    },
  };

  _chartOption.title.text = response.title;

  if (type_graph.value.type == 'bar_percent') _chartOption.xaxis.categories = response.labels;
  else {
    _chartOption.labels = response.labels;
  }

  chartOption.value = { ..._chartOption };
  series.value = response.series;
  loading.value = false;
};

onMounted(async () => {
  await filterHandler();
});
</script>

<template>
  <div>
    <CardSection
      :header-text="type_graph.text"
      wrapperClass="p-5 grid grid-cols-2 gap-4"
    >
      <div v-if="dataNotFound">
        Datos no encontrados
      </div>
      <div class="animate-pulse" v-else-if="loading">
        <div class="h-[20px] w-[300px] mb-[10px] bg-slate-200 rounded"></div>
        <div class="h-[380px] w-[1300px] mb-[10px] bg-slate-200 rounded"></div>
        <div class="h-[20px] w-[1300px] bg-slate-200 rounded"></div>
      </div>
      <div v-else>
        <apexchart width="1300" height="450" :options="chartOption" :series="series"></apexchart>
      </div>
    </CardSection>
  </div>
</template>
