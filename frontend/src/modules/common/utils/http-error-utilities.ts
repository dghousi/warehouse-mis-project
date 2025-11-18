import { type ApiErrorDetail } from '@/types/ApiResponse';

export const parseApiError = (error: unknown): ApiErrorDetail => {
  const fallback: ApiErrorDetail = {
    code: 'UNKNOWN_ERROR',
    details: null,
    message: 'An unexpected error occurred',
  };

  if (typeof error === 'object' && error !== null && 'response' in error) {
    const response = (error as any).response?.data;
    const apiError = response?.error;
    return {
      code: apiError?.code || 'API_ERROR',
      details: apiError?.details ?? null,
      message: apiError?.message || fallback.message,
    };
  }

  if (error instanceof Error) {
    return {
      code: 'ERROR',
      details: null,
      message: error.message,
    };
  }

  return fallback;
};
