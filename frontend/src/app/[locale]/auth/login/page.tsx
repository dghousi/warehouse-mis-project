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
      <div className="bg-muted relative hidden lg:flex items-center justify-center">
        <Image
          src="/paamtech.png"
          alt="Image"
          width={600}
          height={600}
          priority
          className="object-contain dark:brightness-[0.2] dark:grayscale"
        />
      </div>
    </div>
  );
}
