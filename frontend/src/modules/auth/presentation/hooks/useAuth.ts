import { useRouter } from '@/i18n/navigation';
import { LocalStorageService } from '@/modules/common/infrastructure/storage/local-storage-service';
import { useUIStore } from '@/modules/common/store/ui-store';
import { ApiErrorDetail } from '@/types/ApiResponse';
import { useMutation } from '@tanstack/react-query';
import { toast } from 'sonner';
import { useAuthStore } from '../../application/stores/auth-store';
import { LoginUseCase } from '../../application/use-cases/login-use-case';
import { LogoutUseCase } from '../../application/use-cases/logout-use-case';
import { AuthRepository } from '../../infrastructure/repositories/auth-repository';

const authRepository = new AuthRepository();
const loginUseCase = new LoginUseCase(authRepository);
const logoutUseCase = new LogoutUseCase(authRepository);
const storage = new LocalStorageService();

const getErrorMessage = (error?: ApiErrorDetail): string => {
  if (!error) return 'Unknown error';
  if (error.details) {
    const firstKey = Object.keys(error.details)[0];
    const messages = error.details[firstKey];
    return messages?.[0] ?? error.message ?? 'Unknown error';
  }
  return error.message ?? 'Unknown error';
};

type UseAuthResult = {
  isLoggingIn: boolean;
  isLoggingOut: boolean;
  login: (variables: { email: string; password: string }) => void;
  loginError: { message: string } | null;
  logout: () => void;
  logoutError: { message: string } | null;
};

export const useAuth = (): UseAuthResult => {
  const router = useRouter();
  const { markSessionChecked, setUser } = useAuthStore();
  const { setLoading } = useUIStore();

  const loginMutation = useMutation({
    mutationFn: async ({ email, password }: { email: string; password: string }) => {
      setLoading(true);
      const response = await loginUseCase.execute(email, password);
      if (!response.success || !response.data) {
        throw new Error(getErrorMessage(response.error));
      }
      return response.data;
    },
    onError: (error: Error) => {
      toast.error(error.message);
    },
    onSettled: () => {
      setLoading(false);
      markSessionChecked();
    },
    onSuccess: (user) => {
      setUser(user);
      router.push('/dashboard');
    },
  });

  const logoutMutation = useMutation({
    mutationFn: async () => {
      setLoading(true);
      const response = await logoutUseCase.execute();
      if (!response.success) {
        throw new Error(getErrorMessage(response.error) || 'Logout failed');
      }
    },
    onError: (error: Error) => {
      toast.error(error.message);
    },
    onSettled: () => {
      setLoading(false);
    },
    onSuccess: () => {
      storage.clearAll();
      setUser(null);
      toast.success('👋 You’ve been logged out.');
      router.push('/auth/login');
    },
  });

  return {
    isLoggingIn: loginMutation.isPending,
    isLoggingOut: logoutMutation.isPending,
    login: loginMutation.mutate,
    loginError: loginMutation.error ? { message: loginMutation.error.message } : null,
    logout: logoutMutation.mutate,
    logoutError: logoutMutation.error ? { message: logoutMutation.error.message } : null,
  };
};
