<script setup>
import { Link } from '@inertiajs/vue3';

import AuthenticatedLayout from '@Core/Layouts/AuthenticatedLayout.vue';
import CollectionIcon from '@Core/Components/Collection/CollectionIcon.vue';
import CollectionPageHeader from '@Core/Components/Collection/CollectionPageHeader.vue';
import { can } from '@Auth/Services/Auth';

const list = [
  {
    to: 'plants.create.bulk',
    icon: 'potted_plant',
    title: 'Plantas',
    subtitle: 'Carga masiva de plantas desde una planilla.',
  },
  {
    to: 'harvests.create.bulk',
    icon: 'agriculture',
    title: 'Cosechas',
    subtitle: 'Carga masiva de cosechas desde una planilla.',
  },
].filter((ele) => can(ele.to));
</script>

<template>
  <AuthenticatedLayout title="Carga masiva">
    <CollectionPageHeader
      title="Carga masiva"
      description="Accesos rápidos a las cargas masivas disponibles para los recursos del campo."
    />

    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
      <Link
        v-for="ele in list"
        :key="ele.to"
        :href="route(ele.to)"
        class="flex min-h-32 flex-col gap-2 rounded-xl border border-[#e1e9e3] bg-white p-6 shadow-[0_3px_14px_rgba(24,57,39,0.045)] transition hover:border-[#bddcc6] hover:shadow-md"
      >
        <span class="flex size-12 items-center justify-center rounded-lg bg-[#e7f3e9] text-[#17663a]" aria-hidden="true">
          <CollectionIcon :name="ele.icon" :size="28" />
        </span>
        <h2 class="text-base font-bold text-[#102f27]">{{ ele.title }}</h2>
        <p class="text-sm text-[#61716c]">{{ ele.subtitle }}</p>
      </Link>
    </div>
  </AuthenticatedLayout>
</template>
