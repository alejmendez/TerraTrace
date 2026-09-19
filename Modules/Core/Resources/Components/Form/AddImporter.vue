<script setup>
import { ref } from 'vue';

import CollectionDialog from '@Core/Components/Collection/CollectionDialog.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionButton from '@Core/Components/Collection/CollectionButton.vue';

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
    <CollectionButton
        severity="secondary"
        icon="add"
        @click.prevent="open = true"
    />

    <CollectionDialog
        v-model:visible="open"
        title="Agregar un exportador"
        max-width="425px"
    >
        <CollectionInput
            id="importer_name"
            v-model="importer_name"
            :label="__('liquidation.form.importer_id.label')"
        />

        <template #footer>
            <div class="flex justify-end">
                <CollectionButton
                    type="submit"
                    :label="__('generics.actions.create')"
                    :loading="loading"
                    @click="addImporter"
                />
            </div>
        </template>
    </CollectionDialog>
</template>
