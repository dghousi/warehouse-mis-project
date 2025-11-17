'use client';

import { JSX } from 'react';
import { Button } from '@/components/ui/button';
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { cn } from '@/lib/utilities';
import { useAuth } from '@/modules/auth/presentation/hooks/useAuth';
import { useUIStore } from '@/modules/common/store/ui-store';
import { zodResolver } from '@hookform/resolvers/zod';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

const loginSchema = z.object({
  email: z.email(),
  password: z.string().min(1, { message: 'Password is required' }),
});

type LoginFormData = z.infer<typeof loginSchema>;

export const LoginForm = ({ className, ...props }: React.ComponentProps<'form'>): JSX.Element => {
  const { isLoggingIn, login, loginError } = useAuth();
  const { isLoading: uiLoading } = useUIStore();
  const form = useForm<LoginFormData>({
    defaultValues: { email: '', password: '' },
    resolver: zodResolver(loginSchema),
  });

  const onSubmit = (data: LoginFormData): void => {
    login(data);
  };

  return (
    <Form {...form}>
      <form
        onSubmit={form.handleSubmit(onSubmit)}
        className={cn('flex flex-col gap-6 max-w-md w-full', className)}
        {...props}
      >
        <div className="text-center">
          <h1 className="text-2xl font-bold">Login to your account</h1>
        </div>
        <div className="grid gap-6">
          <FormField
            control={form.control}
            name="email"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                  <Input type="email" placeholder="you@example.com" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          <FormField
            control={form.control}
            name="password"
            render={({ field }) => (
              <FormItem>
                <FormLabel>Password</FormLabel>
                <FormControl>
                  <Input type="password" placeholder="Enter your password" {...field} />
                </FormControl>
                <FormMessage />
              </FormItem>
            )}
          />
          {loginError && <div className="text-red-500 text-sm">{loginError.message}</div>}
          <Button type="submit" disabled={isLoggingIn || uiLoading} className="w-full">
            {isLoggingIn || uiLoading ? 'Logging in...' : 'Login'}
          </Button>
        </div>
      </form>
    </Form>
  );
};

export default LoginForm;
