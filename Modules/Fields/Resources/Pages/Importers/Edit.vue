<script setup>
import { Link, useForm } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import CollectionPageHeader from '@Core/Components/Collection/CollectionPageHeader.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';

const props = defineProps({
  data: Object,
});

const form = useForm({
  name: props.data?.name || '',
});

const submitHandler = () => form.put(route('importers.update', props.data.id));
</script>

<template>
  <AuthenticatedLayout title="Editar importador">
    <CollectionPageHeader
      title="Editar importador"
      :description="`Modifica el nombre del importador ${data.name}. El slug se regenera automáticamente.`"
      :action-route="route('importers.index')"
      action-label="Volver al listado"
    />

    <form
      class="mx-auto max-w-2xl rounded-xl border border-[#e1e9e3] bg-white p-6 shadow-[0_3px_14px_rgba(24,57,39,0.045)]"
      @submit.prevent="submitHandler"
    >
      <div class="grid gap-4">
        <CollectionInput
          v-model="form.name"
          label="Nombre"
          :message="form.errors.name"
          autocomplete="off"
          required
        />
      </div>

      <div class="mt-6 flex items-center justify-end gap-3 border-t border-[#e7eee8] pt-4">
        <Link
          :href="route('importers.index')"
          class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-[#d7e0d9] bg-white px-4 py-2.5 font-semibold text-[#284238] transition hover:bg-[#f3f7f4] focus:outline-none focus:ring-2 focus:ring-[#c9e8d1]"
        >
          Cancelar
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-[#17663a] px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-[#11522e] focus:outline-none focus:ring-2 focus:ring-[#17663a] focus:ring-offset-2 disabled:opacity-60"
        >
          {{ form.processing ? 'Guardando...' : 'Actualizar importador' }}
        </button>
      </div>
    </form>
  </AuthenticatedLayout>
</template>
