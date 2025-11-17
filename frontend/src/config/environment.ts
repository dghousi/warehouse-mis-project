type RuntimeEnvironment = 'local' | 'test' | 'production';

const RUNTIME_ENVIRONMENT = (process.env.NEXT_PUBLIC_RUNTIME_ENVIRONMENT ||
  'local') as RuntimeEnvironment;

const required = (name: string, v?: string): string => {
  if (!v) throw new Error(`Missing env var: ${name}`);
  return v;
};

export const APP_ORIGIN = required('NEXT_PUBLIC_SITE_URL', process.env.NEXT_PUBLIC_SITE_URL);

export const API_ORIGIN = required('NEXT_PUBLIC_API_URL', process.env.NEXT_PUBLIC_API_URL);

export const ENV = RUNTIME_ENVIRONMENT;
