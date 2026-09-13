import { definePreset } from '@primevue/themes';
import { CarrotOrangeTheme } from './CarrotOrangeTheme';

export const AppleTheme = definePreset(CarrotOrangeTheme, {
  semantic: {
    primary: {
      50: '#f1fcf2',
      100: '#dff9e4',
      200: '#c1f1cb',
      300: '#90e5a3',
      400: '#58d073',
      500: '#32b550',
      600: '#28a745',
      700: '#1f7634',
      800: '#1e5d2d',
      900: '#1a4d27',
      950: '#092a12',
    },
  },
});
