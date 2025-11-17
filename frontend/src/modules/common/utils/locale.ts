export type Direction = 'ltr' | 'rtl';

export const getDirection = (locale: string): Direction => {
  return ['dr', 'ps'].includes(locale) ? 'rtl' : 'ltr';
};

export const LOCALE_TO_FIELD_SUFFIX: Record<string, string> = {
  dr: 'Dr',
  en: 'En',
  ps: 'Ps',
};

export const getLocalizedFieldKey = (base: string, locale: string): string => {
  const suffix = LOCALE_TO_FIELD_SUFFIX[locale] || LOCALE_TO_FIELD_SUFFIX.en;
  return `${base}${suffix}`;
};
