import { usePage } from '@inertiajs/vue3';

let userPermissions = [];

const loadUserPermissions = () => {
  if (userPermissions.length === 0) {
    const page = usePage();
    userPermissions = [...page.props.auth.user.permissions];
  }
};

export const can = (permission) => {
  loadUserPermissions();
  return userPermissions.includes(permission);
};

export const canShowRightMenu = () => {
  return can('category_products.index') || can('importers.index') || can('owners.index') || can('plant_types.index');
};
