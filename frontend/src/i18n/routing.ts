import { defineRouting } from 'next-intl/routing';

export const routing = defineRouting({
  defaultLocale: 'en',
  locales: ['en', 'dr', 'ps'] as const,
});

export type Locale = (typeof routing.locales)[number];
