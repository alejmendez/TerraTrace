<script setup>
import FormAssignment from './Assignment.vue';
import FormMain from './Main.vue';
import FormComments from './Comments.vue';
import FormResources from './Resources.vue';

import Button from '@Core/Components/Form/Button.vue';

const props = defineProps({
  form: Object,
  submitHandler: Function,
  fields: Array,
  quarters: {
    type: Array,
    default: [],
  },
  plants: {
    type: Array,
    default: [],
  },
  responsibles: Array,
  tools: Array,
  security_equipments: Array,
  machineries: Array,
  task_priorities: Array,
  task_states: Array,
  task_repeat_type: Array,
  task_supplies_units: Array,
});

const task_states = props.task_states;

const form = props.form;

const statesClasses = {
  to_begin: '!bg-gray-500 !border-gray-500 hover:!bg-gray-600 hover:!border-gray-600',
  started: '!bg-cyan-500 !border-cyan-500 hover:!bg-cyan-600 hover:!border-cyan-600',
  finished: '!bg-green-500 !border-green-500 hover:!bg-green-600 hover:!border-green-600',
  stopped: '!bg-red-500 !border-red-500 hover:!bg-red-600 hover:!border-red-600',
  overdued: '!bg-pink-500 !border-pink-500 hover:!bg-pink-600 hover:!border-pink-600',
};
</script>

<template>
  <div class="mt-5">
    <Button
      v-for="state in task_states"
      :class="`me-3 text-l ${statesClasses[state.value]}`"
      @click.prevent="form.status = state"
      :label="state.text"
      :icon="form.status.value === state.value ? 'pi pi-check-square' : 'pi pi-stop'"
    />
  </div>
  <form @submit.prevent="props.submitHandler">
    <FormMain
      :form="form"
      :task_priorities="props.task_priorities"
      :task_states="task_states"
    />
    <FormAssignment
      :form="form"
      :fields="props.fields"
      :quarters="props.quarters"
      :plants="props.plants"
      :responsibles="props.responsibles"
    />
    <FormResources
      :form="form"
      :tools="tools"
      :security_equipments="security_equipments"
      :machineries="machineries"
      :task_supplies_units="props.task_supplies_units"
    />
    <FormComments
      :form="form"
      :responsibles="props.responsibles"
    />
  </form>
</template>
