import { createNavigation } from 'next-intl/navigation';
import { routing } from './routing';

// Create navigation utilities for internationalization
export const { getPathname, Link, redirect, usePathname, useRouter } = createNavigation(routing);
