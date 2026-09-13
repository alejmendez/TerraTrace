<script setup>
import CardSection from '@Core/Components/CardSection.vue';

const props = defineProps({
  field: Object,
});

const field = props.field;
</script>

<template>
  <CardSection
    wrapperClass="p-5 grid grid-cols-1 gap-4"
  >
    <div v-if="field.documents.length === 0">
      <p>{{ __('field.show.documentation.no_documents') }}</p>
    </div>
    <div class="max-w-full mt-2" :title="file.name" v-for="file in field.documents">
      <a :href="file.url" target="_blank">
        {{ file.name }}
      </a>
    </div>
  </CardSection>
</template>
