import { ref } from 'vue';

/**
 * Module-level singleton state for the confirmation dialog.
 * One dialog at a time, matching the PrimeVue useConfirm contract
 * we're replacing. Mount <CollectionConfirmDialog> at the app root
 * (see AuthenticatedLayout) and pass these refs down.
 */
const state = {
    visible: ref(false),
    title: ref(''),
    message: ref(''),
    accept: null,
};

export function useConfirm() {
    return {
        visible: state.visible,
        title: state.title,
        message: state.message,

        /**
         * Queue a confirmation request.
         *
         * @param {object}   opts
         * @param {string}   opts.message
         * @param {string}   [opts.title]   shown as the dialog heading
         * @param {string}   [opts.header]  PrimeVue-compat alias for title
         * @param {function} [opts.accept]  called when the user confirms
         * @param {function} [opts.reject]  accepted for parity; ignored
         */
        ask(opts) {
            state.title.value = opts.title ?? opts.header ?? '';
            state.message.value = opts.message ?? '';
            state.accept = opts.accept ?? null;
            state.visible.value = true;
        },

        confirm() {
            state.visible.value = false;
            const cb = state.accept;
            state.accept = null;
            if (cb) cb();
        },

        cancel() {
            state.visible.value = false;
            state.accept = null;
        },
    };
}
