'use client';

import { useEffect } from 'react';
import { useLocale } from 'next-intl';
import { useRouter } from 'next/navigation';
import { useAuthStore } from '../../application/stores/auth-store';

type ProtectedRouteProps = {
  children: React.ReactNode;
};

export const ProtectedRoute = ({ children }: ProtectedRouteProps): React.ReactNode => {
  const { isSessionChecked, user } = useAuthStore();
  const router = useRouter();
  const locale = useLocale();

  useEffect(() => {
    if (!isSessionChecked) return;
    if (!user) {
      router.replace(`/${locale}/auth/login`);
    }
  }, [user, isSessionChecked, router, locale]);

  if (!isSessionChecked) {
    return null;
  }

  return user ? children : null;
};
