'use client';

import { JSX, useEffect } from 'react';
import { useUIStore } from '@/modules/common/store/ui-store';
import { toast } from 'sonner';
import { useAuthStore } from '../../application/stores/auth-store';
import { AuthRepository } from '../../infrastructure/repositories/auth-repository';

const authRepository = new AuthRepository();

export const AuthInitializer = (): JSX.Element | null => {
  const { markSessionChecked, setUser } = useAuthStore();
  const { setLoading } = useUIStore();

  useEffect((): void => {
    const restoreSession = async (): Promise<void> => {
      setLoading(true);
      try {
        const response = await authRepository.getCurrentUser();
        if (response.success) {
          setUser(response.data);
        } else {
          setUser(null);
          toast.error(response.error?.message || 'Failed to fetch user');
        }
      } catch {
        setUser(null);
        toast.error('Failed to initialize session');
      } finally {
        markSessionChecked();
        setLoading(false);
      }
    };

    restoreSession();
  }, [setUser, markSessionChecked, setLoading]);

  return null;
};
