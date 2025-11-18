import { UseFormSetError } from 'react-hook-form';

export const setServerErrors = (
  errors: Record<string, string[]>,
  setError: UseFormSetError<any>
): void => {
  for (const [field, messages] of Object.entries(errors)) {
    setError(field, { message: messages[0], type: 'server' });
  }
};
