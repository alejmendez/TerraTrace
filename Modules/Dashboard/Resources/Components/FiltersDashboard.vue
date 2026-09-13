<script setup>
import { useForm, router } from '@inertiajs/vue3';

import VSelect from '@Core/Components/Form/VSelect.vue';

const props = defineProps({
  fields: Array,
  field: Object,
});

const form = useForm({
  field_id: props.fields.find((a) => a.value === props.field.data.id),
});

const fieldChangeHandler = () => {
  const url = new URL(window.location.href);
  url.searchParams.set('field_id', form.field_id.value);
  router.get(url);
};
</script>

<template>
  <section class="mt-5 p-5 rounded-xl card-section grid lg:grid-cols-4 md:grid-cols-2 sm:grid-cols-1 gap-x-16 gap-y-4">
    <div class="flex">
      <img class="object-cover object-center w-[72px] h-[72px] rounded-full border-2 border-gray-50" :src="$page.props.auth.user.avatar_url || 'https://i.pravatar.cc/150?img=3'" alt="" />
      <div class="pt-3 ps-2 dark:text-gray-100">
        <div>
          Hola!
        </div>
        <div>
          {{ $page.props.auth.user.full_name }}
        </div>
      </div>
    </div>
    <div class="col-end-5">
      <VSelect
        id="field"
        v-model="form.field_id"
        :placeholder="__('generics.please_select')"
        :options="props.fields"
        :label="__('dashboard.form.field_id.label')"
        @change="fieldChangeHandler"
      />
    </div>
  </section>
</template>
