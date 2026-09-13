import { toRaw } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { can } from '@Auth/Services/Auth';

export const menuElements = (currentComponent) => {
  const page = usePage();
  const menuItems = toRaw(page.props.menu);

  return menuItems;
};

export const menuElementsRight = (currentComponent) => {
  const menuItems = [
    {
      link: route('category_products.index'),
      text: 'menu.right.category_product',
      icon: 'category',
      active: currentComponent.startsWith('Fields::CategoryProducts'),
      can: can('category_products.index'),
    },
    {
      link: route('importers.index'),
      text: 'menu.right.importer',
      icon: 'import_export',
      active: currentComponent.startsWith('Fields::Importer'),
      can: can('importers.index'),
    },
    {
      link: route('owners.index'),
      text: 'menu.right.owner',
      icon: 'person',
      active: currentComponent.startsWith('Fields::Owner'),
      can: can('owners.index'),
    },
    {
      link: route('plant_types.index'),
      text: 'menu.right.plant_type',
      icon: 'potted_plant',
      active: currentComponent.startsWith('Fields::PlantType'),
      can: can('plant_types.index'),
    },
  ];

  const menuItemsFiltered = menuItems.filter((item) => item.can);

  return menuItemsFiltered;
};
