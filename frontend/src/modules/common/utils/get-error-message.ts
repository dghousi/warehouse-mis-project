import { FieldError, FieldErrorsImpl, Merge } from 'react-hook-form';

export const getErrorMessage = (
  error: FieldError | Merge<FieldError, FieldErrorsImpl<any>> | string | undefined,
  t: (key: string) => string
): string | undefined => {
  if (!error) return undefined;
  if (typeof error === 'string') return error;
  if ('message' in error && typeof error.message === 'string') {
    return error.type === 'server' ? error.message : t(error.message);
  }
  return undefined;
};
