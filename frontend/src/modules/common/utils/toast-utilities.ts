import { toast } from 'sonner';

export const notify = {
  error: (message: string, duration = 3000) => toast.error(message, { duration }),
  info: (message: string, duration = 3000) => toast.info(message, { duration }),
  success: (message: string, duration = 3000) => toast.success(message, { duration }),
  warning: (message: string, duration = 3000) => toast.warning(message, { duration }),
};
