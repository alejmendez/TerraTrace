import { definePreset } from '@primevue/themes';
import { CarrotOrangeTheme } from './CarrotOrangeTheme';

export const VulcanTheme = definePreset(CarrotOrangeTheme, {
  semantic: {
    primary: {
      50: '#404350',
      100: '#303342',
      200: '#2a2d3e',
      300: '#7d808b',
      400: '#424654',
      500: '#10121D',
      600: '#0c0e18',
      700: '#070910',
      800: '#020306',
      900: '#020306',
      950: '#020306',
    },
  },
});
