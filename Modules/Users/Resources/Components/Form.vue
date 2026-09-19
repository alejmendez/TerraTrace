<script setup>
import { ref } from 'vue';

import CollectionCardSection from '@Core/Components/Collection/CollectionCardSection.vue';
import CollectionFileInput from '@Core/Components/Collection/CollectionFileInput.vue';
import CollectionInput from '@Core/Components/Collection/CollectionInput.vue';
import CollectionSelect from '@Core/Components/Collection/CollectionSelect.vue';
import CollectionFieldWrapper from '@Core/Components/Collection/CollectionFieldWrapper.vue';
import InputMask from 'primevue/inputmask';

const props = defineProps({
  form: Object,
  roles: Array,
  submitHandler: Function,
  showRole: {
    type: Boolean,
    default: true,
  },
});

const form = props.form;

const avatarPreview = ref(form.avatar);

const changeFileHandler = (e) => {
  form.avatar = e.fileInput;
  form.avatarRemove = e.fileRemove;
};
</script>

<template>
  <form @submit.prevent="props.submitHandler">
    <CollectionCardSection :header-text="__('user.sections.details')">
      <div class="form-text col-span-2 form-text-type">
        <CollectionFileInput
          :image="avatarPreview"
          :imagePreview="true"
          :label="__('generics.form.file.select_a_image')"
          @change="changeFileHandler"
        />
      </div>

      <CollectionInput
        id="dni"
        v-model="form.dni"
        :label="__('user.form.dni.label')"
        :message="form.errors.dni"
      />

      <CollectionInput
        id="name"
        v-model="form.name"
        :label="__('user.form.name.label')"
        :message="form.errors.name"
      />

      <CollectionInput
        id="last_name"
        v-model="form.last_name"
        :label="__('user.form.last_name.label')"
        :message="form.errors.last_name"
      />

      <CollectionInput
        id="email"
        v-model="form.email"
        :label="__('user.form.email.label')"
        :message="form.errors.email"
      />

      <CollectionFieldWrapper :classWrapper="props.classWrapper" :label="__('user.form.phone.label')" :message="form.errors.phone">
        <InputMask
          v-model="form.phone"
          mask="(+99) 9 9999 9999"
          fluid
        />
      </CollectionFieldWrapper>

      <CollectionInput
        id="password"
        type="password"
        v-model="form.password"
        :label="__('user.form.password.label')"
        :message="form.errors.password"
      />
    </CollectionCardSection>
    <CollectionCardSection :header-text="__('user.sections.roles')" v-if="props.showRole">
      <CollectionSelect
        id="role"
        v-model="form.role"
        :placeholder="__('generics.please_select')"
        :options="props.roles"
        :label="__('user.form.role.label')"
        :message="form.errors.role"
      />
    </CollectionCardSection>
  </form>
</template>
