import { trans } from 'laravel-vue-i18n';

import { useConfirm } from '@Core/Composables/useConfirm';
import { useToast } from '@Core/Composables/useToast';

/**
 * Open a confirmation dialog before deleting a row. Used by the
 * Show pages, which navigate away after deletion.
 *
 * @param  {() => void | Promise<void>}  accept  delete action
 * @param  {string}  [entity]  i18n key suffix for the entity name
 */
export const deleteRowTable = (accept, entity = null) => {
    useConfirm().ask({
        message: trans('generics.tables.confirm.delete', { entity: entity ?? trans('generics.tables.entity') }),
        title: trans('generics.tables.confirm.delete_header', { entity: entity ?? trans('generics.tables.entity') }),
        accept,
    });
};

/**
 * Open a confirmation dialog before deleting a row from a table.
 * On success, reloads the datatable and shows a toast. Used by
 * the harvest / datatable components that refresh after deletion.
 *
 * The first two positional args (`confirm`, `toast`) are accepted
 * for backwards compatibility but ignored — the module-level
 * composables handle them now. `HarvestTable.vue` still calls with
 * the old shape and will be migrated separately.
 *
 * @param  {object}  _confirm   legacy PrimeVue confirm instance (ignored)
 * @param  {{value: object, loadLazyData: () => void}}  datatable
 * @param  {object}  _toast     legacy PrimeVue toast instance (ignored)
 * @param  {() => Promise<boolean>}  fetchDelete
 */
export const defaultDeleteHandler = (_confirm, datatable, _toast, fetchDelete) => {
    useConfirm().ask({
        message: trans('generics.tables.confirm.delete', { entity: trans('generics.tables.entity') }),
        title: trans('generics.tables.confirm.delete_header', { entity: trans('generics.tables.entity') }),
        accept: async () => {
            const ok = await fetchDelete();
            if (ok) {
                datatable.value.loadLazyData();
                useToast().show({
                    tone: 'success',
                    message: trans('generics.messages.deleted_successfully'),
                });
                return;
            }
            useToast().show({
                tone: 'error',
                message: trans('generics.tables.errors.could_not_delete_the_record'),
            });
        },
    });
};
