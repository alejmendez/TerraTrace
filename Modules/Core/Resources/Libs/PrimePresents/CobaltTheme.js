import { definePreset } from '@primevue/themes';
import { CarrotOrangeTheme } from './CarrotOrangeTheme';

export const CobaltTheme = definePreset(CarrotOrangeTheme, {
  semantic: {
    primary: {
      50: '#f0f6fe',
      100: '#ddeafc',
      200: '#c3dbfa',
      300: '#9ac5f6',
      400: '#6aa6f0',
      500: '#4785ea',
      600: '#3268de',
      700: '#2954cc',
      800: '#2846a7',
      900: '#253d83',
      950: '#1b2750',
    },
  },
});
