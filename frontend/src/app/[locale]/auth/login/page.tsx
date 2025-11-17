'use client';

import { JSX } from 'react';
import Image from 'next/image';
import { AuthRedirect } from '@/modules/auth/presentation/components/auth-redirect';
import LoginForm from '@/modules/auth/presentation/components/login-form';

// eslint-disable-next-line react/function-component-definition
export default function Page(): JSX.Element {
  return (
    <div className="grid min-h-svh lg:grid-cols-2">
      <div className="flex flex-col gap-4 p-6 md:p-10">
        <div className="flex flex-1 items-center justify-center">
          <div className="w-full max-w-xs">
            <AuthRedirect />
            <LoginForm />
          </div>
        </div>
      </div>
      <div className="bg-muted relative hidden lg:block">
        <Image
          src="/login-background-2.png"
          fill
          priority
          alt="Image"
          sizes="(max-width: 1024px) 0px, 50vw"
          className="absolute inset-0 h-full w-full object-cover dark:brightness-[0.2] dark:grayscale"
        />
      </div>
    </div>
  );
}
