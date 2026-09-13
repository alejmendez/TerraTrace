import { definePreset } from '@primevue/themes';
import { CarrotOrangeTheme } from './CarrotOrangeTheme';

export const DodgerBlueTheme = definePreset(CarrotOrangeTheme, {
  semantic: {
    primary: {
      50: '#eef7ff',
      100: '#d9edff',
      200: '#bce1ff',
      300: '#8ecfff',
      400: '#59b2ff',
      500: '#2c8eff',
      600: '#1b71f5',
      700: '#145be1',
      800: '#1749b6',
      900: '#19418f',
      950: '#142957',
    },
  },
});
