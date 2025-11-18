'use client';

import { useTransition } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import Image from 'next/image';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { usePathname, useRouter } from '@/i18n/navigation';
import { routing } from '@/i18n/routing';
import { AxiosClient } from '@/modules/common/infrastructure/api/axios-client';
import Cookies from 'js-cookie';

const localeNames: Record<string, string> = {
  dr: 'دری',
  en: 'English',
  ps: 'پښتو',
};

const localeFlags: Record<string, string> = {
  dr: '/afg.svg',
  en: '🇺🇸',
  ps: '/afg.svg',
};

const client = new AxiosClient();

export const LocaleSwitcher = (): React.ReactElement => {
  const currentLocale = useLocale();
  const router = useRouter();
  const pathname = usePathname();
  const [isPending, startTransition] = useTransition();
  const t = useTranslations('component');

  const fetchCsrf = async (retries = 2, delayMs = 100): Promise<void> => {
    for (let attempt = 1; attempt <= retries; attempt++) {
      try {
        await client.get('/sanctum/csrf-cookie', { headers: { Accept: 'application/json' } });
        const xsrfToken = Cookies.get('XSRF-TOKEN');
        if (!xsrfToken) throw new Error('XSRF-TOKEN cookie not set');
        return;
      } catch (error) {
        if (attempt === retries) throw new Error(`Failed to fetch CSRF token: ${error}`);
        await new Promise((resolve) => setTimeout(resolve, delayMs));
      }
    }
  };

  const checkAuth = async (): Promise<boolean> => {
    try {
      const response = await client.get('/api/user');
      return response.success && !!response.data;
    } catch {
      return false;
    }
  };

  const changeLocale = (newLocale: string): void => {
    if (!routing.locales.includes(newLocale as (typeof routing.locales)[number])) return;

    startTransition(async () => {
      try {
        const isAuthenticated = await checkAuth();
        if (!isAuthenticated) {
          router.push(pathname, { locale: newLocale });
          return;
        }

        await fetchCsrf();
        await client.post(
          '/api/user/locale',
          { locale: newLocale },
          { headers: { Accept: 'application/json' } }
        );
        router.push(pathname, { locale: newLocale });
      } catch (error) {
        console.error('Failed to change locale:', error);
      }
    });
  };

  return (
    <Select value={currentLocale} onValueChange={changeLocale} disabled={isPending}>
      <SelectTrigger className="w-[120px] rounded border px-2 py-1 ms-2">
        <SelectValue placeholder={t('selectLocale', { defaultValue: 'Select Locale' })} />
      </SelectTrigger>
      <SelectContent>
        <SelectGroup>
          {routing.locales.map((locale) => (
            <SelectItem key={locale} value={locale}>
              <div className="flex items-center gap-2">
                {localeFlags[locale].startsWith('/') ? (
                  <div className="relative w-[34px] h-auto aspect-[34/16]">
                    <Image
                      src={localeFlags[locale]}
                      alt={`${locale} flag`}
                      fill
                      className="object-contain rounded-sm"
                      loading="lazy"
                    />
                  </div>
                ) : (
                  <span>{localeFlags[locale]}</span>
                )}
                <span>{localeNames[locale]}</span>
              </div>
            </SelectItem>
          ))}
        </SelectGroup>
      </SelectContent>
    </Select>
  );
};
