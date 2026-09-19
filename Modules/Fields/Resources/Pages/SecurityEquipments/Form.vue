<script setup>
import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';

const props = defineProps({
  form: Object,
  submitHandler: Function,
});

const form = props.form;
</script>

<style>
textarea.p-textarea.note {
  margin-top: 0;
  border-radius: 0;
  border: none;
}
</style>

<template>
  <form @submit.prevent="props.submitHandler">
    <CollectionCardSection :header-text="__('security_equipment.sections.details')">
      <CollectionInput
        id="name"
        v-model="form.name"
        :label="__('security_equipment.form.name.label')"
        :message="form.errors.name"
      />

      <CollectionInput
        id="purchase_date"
        type="date"
        v-model="form.purchase_date"
        :label="__('security_equipment.form.purchase_date.label')"
        :message="form.errors.purchase_date"
        :maxDate="new Date()"
      />

      <CollectionInput
        id="last_maintenance"
        type="date"
        v-model="form.last_maintenance"
        :label="__('security_equipment.form.last_maintenance.label')"
        :message="form.errors.last_maintenance"
        :maxDate="new Date()"
      />

      <CollectionInput
        id="purchase_location"
        v-model="form.purchase_location"
        :label="__('security_equipment.form.purchase_location.label')"
        :message="form.errors.purchase_location"
      />

      <CollectionInput
        id="type"
        v-model="form.type"
        :label="__('security_equipment.form.type.label')"
        :message="form.errors.type"
      />

      <CollectionInput
        id="contact"
        v-model="form.contact"
        :label="__('security_equipment.form.contact.label')"
        :message="form.errors.contact"
      />
    </CollectionCardSection>

    <CollectionCardSection :header-text="__('security_equipment.sections.note')" wrapperClass="">
      <CollectionInput
        id="note"
        type="textarea"
        class="min-h-36 note"
        v-model="form.note"
        :placeholder="__('security_equipment.form.note.placeholder')"
        :message="form.errors.note"
      />
    </CollectionCardSection>
  </form>
</template>
