import { trans } from 'laravel-vue-i18n';
import { parse, parseISO, format } from 'date-fns';

export const getAge = (birthDate) => Math.floor((new Date() - new Date(birthDate).getTime()) / 3.15576e10);
export const stringToDate = (date) => {
  // Verifica si la fecha contiene una 'T', indicando un formato ISO
  if (date === null) {
    return null;
  }
  if (date.includes('T')) {
    return parseISO(date);
  }
  return parse(date, 'yyyy-MM-dd', new Date());
};

export const dateToString = (date, f = 'dd/MM/yyyy') => {
  return format(date, f);
};

export const stringToFormat = (date, f = 'dd/MM/yyyy') => {
  return format(stringToDate(date), f);
};

const msPerMinute = 60 * 1000;
const msPerHour = msPerMinute * 60;
const msPerDay = msPerHour * 24;
const msPerMonth = msPerDay * 30;
const msPerYear = msPerDay * 365;

export const relativeTimeDifference = (current, previous) => {
  const elapsed = current - previous;
  let message = '';
  let time = 0;

  if (elapsed < msPerMinute) {
    message = 'generics.date.seconds_ago';
    time = Math.round(elapsed / 1000);
  } else if (elapsed < msPerHour) {
    message = 'generics.date.minutes_ago';
    time = Math.round(elapsed / msPerMinute);
  } else if (elapsed < msPerDay) {
    message = 'generics.date.hours_ago';
    time = Math.round(elapsed / msPerHour);
  } else if (elapsed < msPerMonth) {
    message = 'generics.date.days_ago';
    time = Math.round(elapsed / msPerDay);
  } else if (elapsed < msPerYear) {
    message = 'generics.date.months_ago';
    time = Math.round(elapsed / msPerMonth);
  } else {
    message = 'generics.date.years_ago';
    time = Math.round(elapsed / msPerYear);
  }

  return trans(message, { time });
};
