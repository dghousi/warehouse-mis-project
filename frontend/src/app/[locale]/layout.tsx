import '../globals.css';
import { JSX } from 'react';
import type { Metadata } from 'next';
import { NextIntlClientProvider } from 'next-intl';
import localFont from 'next/font/local';
import { notFound } from 'next/navigation';
import { ClientLayoutWrapper } from '@/components/client-layout-wrapper';
import { ReactQueryProvider } from '@/components/react-query-provider';
import { LoadingOverlay } from '@/components/shared/loading-overlay';
import { Toaster } from '@/components/ui/sonner';
import { routing } from '@/i18n/routing';
import { AuthInitializer } from '@/modules/auth/presentation/components/auth-initializer';
import { getDirection } from '@/modules/common/utils/locale';

// Fonts
const geistSans = localFont({
  display: 'swap',
  src: '../../../public/fonts/geist/Geist-Regular.woff2',
  variable: '--font-geist-sans',
});

const geistMono = localFont({
  display: 'swap',
  src: '../../../public/fonts/geistMono/GeistMono-Regular.woff2',
  variable: '--font-geist-mono',
});

export const metadata: Metadata = {
  description: 'Developed By AOP',
  title: 'CPAMIS',
};

// eslint-disable-next-line react/function-component-definition
export default async function RootLayout({
  children,
  params,
}: LayoutProps<'/[locale]'>): Promise<JSX.Element> {
  const { locale } = await params;

  if (!routing.locales.includes(locale as (typeof routing.locales)[number])) {
    notFound();
  }

  const direction = getDirection(locale);

  return (
    <html lang={locale} className="light">
      <body className={`${geistSans.variable} ${geistMono.variable} antialiased`} dir={direction}>
        <NextIntlClientProvider locale={locale}>
          <ReactQueryProvider>
            <AuthInitializer />
            <LoadingOverlay />
            <Toaster position="top-center" richColors duration={4000} />
            <ClientLayoutWrapper>{children}</ClientLayoutWrapper>
          </ReactQueryProvider>
        </NextIntlClientProvider>
      </body>
    </html>
  );
}

export function generateStaticParams(): { locale: string }[] {
  return routing.locales.map((locale) => ({ locale }));
}
