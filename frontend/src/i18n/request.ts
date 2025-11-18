import { getRequestConfig } from 'next-intl/server';
import { cookies } from 'next/headers';
import { routing } from './routing';

type Messages = {
app: Record<string, unknown>;
  auth: Record<string, unknown>;
  component: Record<string, unknown>;
  global: Record<string, unknown>;
  sidebar: Record<string, unknown>;
  user: Record<string, unknown>;
  role: Record<string, unknown>;
  permission: Record<string, unknown>;
};

export default getRequestConfig(async ({ requestLocale }) => {
  let locale = (await requestLocale) || routing.defaultLocale;

  const cookieLocale = (await cookies()).get('NEXT_LOCALE')?.value;
  if (cookieLocale && routing.locales.includes(cookieLocale as (typeof routing.locales)[number])) {
    locale = cookieLocale;
  }

  if (!routing.locales.includes(locale as (typeof routing.locales)[number])) {
    locale = routing.defaultLocale;
  }

  const messages: Messages = {
app: (await import(`@/messages/${locale}/app.json`)).default,
    auth: (await import(`@/messages/${locale}/auth.json`)).default,
    component: (await import(`@/messages/${locale}/component.json`)).default,
    global: (await import(`@/messages/${locale}/global.json`)).default,
    permission: (await import(`@/messages/${locale}/user-management/permission.json`)).default,
    role: (await import(`@/messages/${locale}/user-management/role.json`)).default,
    sidebar: (await import(`@/messages/${locale}/sidebar.json`)).default,
    user: (await import(`@/messages/${locale}/user-management/user.json`)).default,
  };

  return { locale, messages };
});
