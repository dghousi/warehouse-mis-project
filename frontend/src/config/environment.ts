type RuntimeEnvironment = 'local' | 'test' | 'production';

const RUNTIME_ENVIRONMENT = (process.env.NEXT_PUBLIC_RUNTIME_ENVIRONMENT || 'local') as RuntimeEnvironment;

export const APP_ORIGIN = process.env.NEXT_PUBLIC_SITE_URL || "http://localhost:3000";

export const API_ORIGIN = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000";

export const ENV = RUNTIME_ENVIRONMENT;
