<script setup>
import CollectionIcon from './CollectionIcon.vue';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: '',
    },
    maxWidth: {
        type: String,
        default: '28rem',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['update:visible', 'close']);

function close() {
    emit('update:visible', false);
    emit('close');
}

function onBackdropClick() {
    if (props.closeable) close();
}

function stopClick(e) {
    e.stopPropagation();
}
</script>

<template>
    <Teleport to="body">
        <div
            v-if="props.visible"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#102f27]/35 p-4"
            role="presentation"
            @click="onBackdropClick"
        >
            <section
                class="flex max-h-[90vh] w-full flex-col rounded-xl bg-white shadow-2xl"
                :style="{ maxWidth: props.maxWidth }"
                role="dialog"
                aria-modal="true"
                :aria-label="props.title"
                @click="stopClick"
            >
                <header
                    v-if="props.title !== '' || $slots.header || props.closeable"
                    class="flex shrink-0 items-center justify-between gap-4 border-b border-[#e1e9e3] px-6 py-4"
                >
                    <div class="grow">
                        <slot name="header">
                            <h2 class="text-lg font-extrabold text-[#102f27]">
                                {{ props.title }}
                            </h2>
                        </slot>
                    </div>
                    <button
                        v-if="props.closeable"
                        type="button"
                        class="shrink-0 text-[#61716c] hover:text-[#102f27]"
                        aria-label="Cerrar"
                        @click="close"
                    >
                        <CollectionIcon name="close" :size="20" aria-hidden="true" />
                    </button>
                </header>

                <div class="grow overflow-y-auto px-6 py-4">
                    <slot />
                </div>

                <footer
                    v-if="$slots.footer"
                    class="shrink-0 border-t border-[#e1e9e3] px-6 py-4"
                >
                    <slot name="footer" />
                </footer>
            </section>
        </div>
    </Teleport>
</template>
