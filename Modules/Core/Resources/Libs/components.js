import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';

import Tooltip from 'primevue/tooltip';

export const initComponent = (app) => {
  app.use(ToastService);
  app.use(ConfirmationService);

  app.directive('tooltip', Tooltip);
};
