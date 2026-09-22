<script setup>
import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionMultiSelect from '@Core/Components/Collection/CollectionMultiSelect.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';

const props = defineProps({
  form: Object,
  tools: Array,
  security_equipments: Array,
  machineries: Array,
  task_supplies_units: Array,
});

const form = props.form;

const units = props.task_supplies_units;

const add_supply = () => {
  form.supplies.push({
    id: null,
    name: null,
    brand: null,
    quantity: null,
    unit: '',
  });
};

const remove_supply = (index) => {
  form.supplies.splice(index, 1);
};
</script>

<template>
  <CollectionCardSection :header-text="__('task.sections.resources')" wrapperClass="">
    <div class="p-6 grid md:grid-cols-3 gap-x-16 gap-y-4 sm:grid-cols-1">
      <CollectionMultiSelect
        class="mt-1"
        v-model="form.tools"
        :options="props.tools"
        :label="__('task.form.tools.label')"
        :placeholder="__('generics.please_select')"
        :message="form.errors.tools"
      />

      <CollectionMultiSelect
        class="mt-1"
        v-model="form.machineries"
        :options="props.machineries"
        :label="__('task.form.machineries.label')"
        :placeholder="__('generics.please_select')"
        :message="form.errors.machineries"
      />

      <CollectionMultiSelect
        class="mt-1"
        v-model="form.security_equipments"
        :options="props.security_equipments"
        :label="__('task.form.security_equipments.label')"
        :placeholder="__('generics.please_select')"
        :message="form.errors.security_equipments"
      />
    </div>

    <div
      class="px-6 py-2 grid grid-cols-2 gap-x-16 gap-y-4"
      v-for="(supply, index) in form.supplies"
    >
      <CollectionInput
        :id="`supplies_name_${index}`"
        v-model="supply.name"
        :label="__('task.form.supplies.name.label')"
        :message="form.errors[`supplies.${index}.name`]"
      />
      <!-- class="col-span-4" -->
      <div class="grid grid-cols-10">
        <div class="grid grid-cols-3 gap-x-4 gap-y-4 col-span-9">
          <CollectionInput
            :id="`supplies_brand_${index}`"
            v-model="supply.brand"
            :label="__('task.form.supplies.brand.label')"
            :message="form.errors[`supplies.${index}.brand`]"
          />
          <CollectionInput
            :id="`supplies_quantity_${index}`"
            v-model="supply.quantity"
            type="number"
            :min="0"
            :max="2000"
            :step="0.01"
            :label="__('task.form.supplies.quantity.label')"
            :message="form.errors[`supplies.${index}.quantity`]"
          />

          <CollectionSelect
            :id="`supplies_unit_${index}`"
            v-model="supply.unit"
            :placeholder="__('generics.please_select')"
            :options="units"
            :label="__('task.form.supplies.unit.label')"
            :message="form.errors[`supplies.${index}.unit`]"
          />
        </div>
        <div class="pt-10" v-if="index !== 0">
          <CollectionIcon
            name="delete"
            :size="22"
            class="float-right me-3 text-gray-800 dark:text-gray-100 hover:text-orange-700 dark:hover:text-orange-700"
            @click="remove_supply(index)"
          />
        </div>
      </div>
    </div>

    <div class="p-6">
      <CollectionButton @click.prevent="add_supply" :label="__('task.buttons.add_supply')" />
    </div>
  </CollectionCardSection>
</template>
