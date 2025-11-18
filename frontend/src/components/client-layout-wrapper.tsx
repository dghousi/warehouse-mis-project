'use client';

import { useLocale } from 'next-intl';
import { usePathname } from 'next/navigation';
import { AppShell } from '@/components/app-shell';
import { ProtectedRoute } from '@/modules/auth/presentation/components/protected-route';

type ClientLayoutWrapperProps = {
  children: React.ReactNode;
};

export const ClientLayoutWrapper = ({ children }: ClientLayoutWrapperProps): React.ReactNode => {
  const pathname = usePathname();
  const locale = useLocale();

  const isAuthPage = pathname.startsWith(`/${locale}/auth`);

  return isAuthPage ? (
    children
  ) : (
    <ProtectedRoute>
      <AppShell>{children}</AppShell>
    </ProtectedRoute>
  );
};
