<script setup>
import { Link } from '@inertiajs/vue3';

import Button from '@Core/Components/Form/Button.vue';
import CollectionIcon from './CollectionIcon.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },

    /**
     * Optional breadcrumb trail rendered above the title. Each entry
     * is `{ to: routeName | null, text: i18nKey }` — `to: null` means
     * the entry is rendered as plain text (terminal segment).
     * Replaces `Modules\Core\Components\Crud\BreadCrumbs.vue`.
     */
    breadcrumbs: {
        type: Array,
        default: () => [],
    },

    /**
     * Optional array of header-level action buttons rendered in the
     * right column. Each entry is
     * `{ to: routeName | url | function, text: i18nKey, variant?: string }`.
     * `to` as a function is treated as an onClick handler.
     */
    links: {
        type: Array,
        default: () => [],
    },

    /**
     * Optional form-action bundle:
     *   `{ instance: useFormReturn, submitHandler: () => void,
     *      submitText?: string, hrefCancel?: string }`.
     * When present, renders a submit button (with processing state
     * wired to `instance.processing`) and an optional cancel link.
     */
    form: {
        type: Object,
        default: null,
    },

    /**
     * Optional primary CTA rendered as a green link with a `+` icon
     * (the canonical "add new" button used on List pages).
     */
    actionLabel: {
        type: String,
        default: '',
    },
    actionRoute: {
        type: String,
        default: '',
    },
});

const isAbsoluteUrl = (s) => typeof s === 'string' && s.toLowerCase().startsWith('http');
const resolveHref = (to) => (isAbsoluteUrl(to) ? to : route(to));
</script>

<template>
    <header class="terra-page-header flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="grow">
            <nav
                v-if="props.breadcrumbs.length"
                class="breadcrumbs py-4 flex items-center flex-wrap text-gray-500 text-sm font-medium"
                aria-label="Breadcrumb"
            >
                <ul class="flex items-center">
                    <li
                        v-for="(entry, index) in props.breadcrumbs"
                        :key="index"
                        class="inline-flex items-center"
                    >
                        <Link
                            v-if="entry.to"
                            :href="resolveHref(entry.to)"
                            class="text-gray-600 hover:text-[#17663a] dark:text-gray-100"
                        >
                            {{ entry.text }}
                        </Link>
                        <span v-else class="text-gray-600">{{ entry.text }}</span>

                        <CollectionIcon
                            v-if="index < props.breadcrumbs.length - 1"
                            name="chevron_right"
                            :size="16"
                            class="mx-2 text-gray-400"
                        />
                    </li>
                </ul>
            </nav>

            <slot name="header" />

            <h1 class="text-3xl font-extrabold tracking-[-0.035em] text-[#102f27] dark:text-gray-100 sm:text-3xl md:text-4xl">
                {{ title }}
            </h1>
            <p
                v-if="description"
                class="mt-2 max-w-2xl text-base text-[#61716c]"
            >
                {{ description }}
            </p>
        </div>

        <div
            class="gap-3 flex flex-wrap items-center justify-start shrink-0"
            :class="{ 'md:mt-[50px]': props.breadcrumbs.length }"
        >
            <template v-for="link in props.links" :key="link.text">
                <Button
                    v-if="typeof link.to === 'function'"
                    :severity="link.variant || 'secondary'"
                    :label="__(link.text)"
                    @click="link.to"
                />
                <Button
                    v-else
                    :severity="link.variant || 'secondary'"
                    :href="resolveHref(link.to)"
                    :label="__(link.text)"
                />
            </template>

            <slot />

            <template v-if="props.form">
                <Button
                    :disabled="props.form.instance?.processing"
                    :loading="props.form.instance?.processing"
                    :label="props.form.submitText || __('generics.buttons.save')"
                    @click="props.form.submitHandler"
                />
                <Button
                    v-if="props.form.hrefCancel"
                    severity="secondary"
                    :disabled="props.form.instance?.processing"
                    :loading="props.form.instance?.processing"
                    :href="props.form.hrefCancel"
                    :label="__('generics.buttons.cancel')"
                />
            </template>

            <Link
                v-if="actionRoute && actionLabel"
                :href="actionRoute"
                class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-[#17663a] px-4 py-2.5 font-semibold text-white shadow-sm transition hover:bg-[#11522e] focus:outline-none focus:ring-2 focus:ring-[#17663a] focus:ring-offset-2"
            >
                <CollectionIcon name="add" :size="20" aria-hidden="true" />
                {{ actionLabel }}
            </Link>
        </div>
    </header>
</template>
