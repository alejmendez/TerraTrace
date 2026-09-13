<script setup>
import BreadCrumbs from '@Core/Components/Crud/BreadCrumbs.vue';
import Button from '@Core/Components/Form/Button.vue';

const props = defineProps({
  breadcrumbs: Array,
  title: String,
  links: Array,
  form: Object,
});

const isPromise = (obj) => obj instanceof Promise;
const isFunction = (obj) => obj instanceof Function;
const isLink = (str) => str.toLowerCase().startsWith('http');
</script>

<template>
  <header class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div class="grow ">
      <BreadCrumbs :elements="props.breadcrumbs" />

      <h1
        class="mt-4 mb-5 text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100 sm:text-2xl md:text-3xl"
      >
        {{ props.title }}
      </h1>
    </div>

    <div
      class="gap-3 flex flex-wrap items-center justify-start shrink-0 md:mt-[50px]"
      v-if="props.links"
    >
      <template
        v-for="link in props.links"
        :key="link.text"
      >
        <template v-if="isPromise(link.to) || isFunction(link.to)">
          <Button :severity="link.variant" @click="link.to" :label="__(link.text)" />
        </template>
        <template v-else>
          <Button
            :key="link.text"
            :severity="link.variant"
            :href="isLink(link.to) ? link.to : route(link.to)"
            :label="__(link.text)"
          />
        </template>
      </template>
    </div>

    <div
      class="gap-3 flex flex-wrap items-center justify-start shrink-0 md:mt-[50px]"
    >
      <slot></slot>

      <Button
        :disabled="props.form?.instance.processing"
        @click="props.form?.submitHandler"
        v-if="props.form?.submitHandler"
        :loading="props.form?.instance.processing"
        :label="props.form?.submitText"
      />

      <Button
        severity="secondary"
        :disabled="props.form?.instance.processing"
        :href="props.form?.hrefCancel"
        :loading="props.form?.instance.processing"
        :label="__('generics.buttons.cancel')"
        v-if="props.form?.hrefCancel"
      />
    </div>
  </header>
</template>
