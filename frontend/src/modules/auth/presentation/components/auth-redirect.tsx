'use client';

import { JSX, useEffect } from 'react';
import { useRouter } from 'next/navigation';
import { useAuthStore } from '../../application/stores/auth-store';

export const AuthRedirect = (): JSX.Element | null => {
  const { isSessionChecked, user } = useAuthStore();
  const router = useRouter();

  useEffect(() => {
    if (!isSessionChecked) return;
    if (user) {
      router.replace('/dashboard');
    }
  }, [user, isSessionChecked, router]);

  return null;
};
