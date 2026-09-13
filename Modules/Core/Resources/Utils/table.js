import { trans } from 'laravel-vue-i18n';

const createConfirmOptions = (entity, accept) => ({
  message: trans('generics.tables.confirm.delete', { entity }),
  header: trans('generics.tables.confirm.delete_header', { entity }),
  icon: 'pi pi-info-circle',
  rejectLabel: trans('generics.tables.confirm.denyButton'),
  rejectProps: {
    label: trans('generics.tables.confirm.denyButton'),
    severity: 'secondary',
    outlined: true,
  },
  acceptProps: {
    label: trans('generics.tables.confirm.confirmButton'),
    severity: 'danger',
  },
  accept,
  reject: () => {},
});

const showToast = (toast, isSuccess = true) => {
  const config = isSuccess
    ? {
        severity: 'success',
        summary: trans('generics.messages.deleted_successfully_summary'),
        detail: trans('generics.messages.deleted_successfully'),
      }
    : {
        severity: 'error',
        summary: trans('generics.tables.errors.could_not_delete_the_record_summary'),
        detail: trans('generics.tables.errors.could_not_delete_the_record'),
      };

  toast.add({
    ...config,
    life: 3000,
  });
};

const handleDeleteConfirmation = async (handler, datatable, toast) => {
  const result = await handler();

  if (result) {
    datatable.value.loadLazyData();
    showToast(toast, true);
    return;
  }

  showToast(toast, false);
};

export const deleteRowTable = async (confirm, accept, entity = null) => {
  const confirmOptions = createConfirmOptions(entity || trans('generics.tables.entity'), accept);
  confirm.require(confirmOptions);
};

export const defaultDeleteHandler = (confirm, datatable, toast, fetchDelete) => {
  deleteRowTable(confirm, async () => {
    const result = await fetchDelete();
    if (result) {
      datatable.value.loadLazyData();
      return toast.add({
        severity: 'success',
        summary: trans('generics.messages.deleted_successfully_summary'),
        detail: trans('generics.messages.deleted_successfully'),
        life: 3000,
      });
    }
    toast.add({
      severity: 'error',
      summary: trans('generics.tables.errors.could_not_delete_the_record_summary'),
      detail: trans('generics.tables.errors.could_not_delete_the_record'),
      life: 3000,
    });
  });
};

export const deleteRowDatatable = (options) => {
  const { datatable, confirm, toast, entity = trans('generics.tables.entity'), handler } = options;

  const confirmOptions = createConfirmOptions(entity, async () => await handleDeleteConfirmation(handler, datatable, toast));

  confirm.require(confirmOptions);
};
