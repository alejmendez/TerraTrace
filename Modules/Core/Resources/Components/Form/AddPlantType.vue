<script setup>
import { ref } from 'vue';

import CollectionDialog from '@Core/Components/Collection/CollectionDialog.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';

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
    <CollectionButton
        severity="secondary"
        icon="add"
        @click.prevent="open = true"
    />

    <CollectionDialog
        v-model:visible="open"
        title="Agregar un tipo de planta"
        max-width="425px"
    >
        <CollectionInput
            id="plant_type_name"
            v-model="plant_type_name"
            :label="__('plant.form.plant_type_id.label')"
        />

        <template #footer>
            <div class="flex justify-end">
                <CollectionButton
                    type="submit"
                    :label="__('generics.actions.create')"
                    :loading="loading"
                    @click="addPlantType"
                />
            </div>
        </template>
    </CollectionDialog>
</template>
