import createMiddleware from 'next-intl/middleware';
import { NextRequest, NextResponse } from 'next/server';
import { routing } from './i18n/routing';

const intlMiddleware = createMiddleware(routing);

const COOKIE_NAME = 'NEXT_LOCALE';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 365; // 1 year

const middleware = (request: NextRequest): NextResponse => {
  const pathname = request.nextUrl.pathname;
  const response = intlMiddleware(request);

  const localeInPath = pathname.split('/')[1];

  if (localeInPath && routing.locales.includes(localeInPath as (typeof routing.locales)[number])) {
    response.cookies.set(COOKIE_NAME, localeInPath, {
      maxAge: COOKIE_MAX_AGE,
      path: '/',
    });
  } else {
    const savedLocale = request.cookies.get(COOKIE_NAME)?.value;
    if (savedLocale && routing.locales.includes(savedLocale as (typeof routing.locales)[number])) {
      return NextResponse.redirect(new URL(`/${savedLocale}${pathname}`, request.url));
    }
  }

  return response;
};

export const config = {
  matcher: ['/((?!api|_next|_vercel|.*\\..*).*)'], //NOSONAR
};

export default middleware;
