'use client';

import * as React from 'react';
import { JSX } from 'react';
import { toggleVariants } from '@/components/ui/toggle';
import { cn } from '@/lib/utilities';
import * as ToggleGroupPrimitive from '@radix-ui/react-toggle-group';
import { type VariantProps } from 'class-variance-authority';

const ToggleGroupContext = React.createContext<VariantProps<typeof toggleVariants>>({
  size: 'default',
  variant: 'default',
});

const ToggleGroup = ({
  children,
  className,
  size,
  variant,
  ...props
}: React.ComponentProps<typeof ToggleGroupPrimitive.Root> &
  VariantProps<typeof toggleVariants>): JSX.Element => {
  const contextValue = React.useMemo(() => ({ size, variant }), [variant, size]);

  return (
    <ToggleGroupPrimitive.Root
      data-slot="toggle-group"
      data-variant={variant}
      data-size={size}
      className={cn(
        'group/toggle-group flex w-fit items-center rounded-md data-[variant=outline]:shadow-xs',
        className
      )}
      {...props}
    >
      <ToggleGroupContext.Provider value={contextValue}>{children}</ToggleGroupContext.Provider>
    </ToggleGroupPrimitive.Root>
  );
};

const ToggleGroupItem = ({
  children,
  className,
  size,
  variant,
  ...props
}: React.ComponentProps<typeof ToggleGroupPrimitive.Item> &
  VariantProps<typeof toggleVariants>): JSX.Element => {
  const context = React.useContext(ToggleGroupContext);

  return (
    <ToggleGroupPrimitive.Item
      data-slot="toggle-group-item"
      data-variant={context.variant ?? variant}
      data-size={context.size ?? size}
      className={cn(
        toggleVariants({
          size: context.size ?? size,
          variant: context.variant ?? variant,
        }),
        'min-w-0 flex-1 shrink-0 rounded-none shadow-none first:rounded-s-md last:rounded-e-md focus:z-10 focus-visible:z-10 data-[variant=outline]:border-s-0 data-[variant=outline]:first:border-s',
        className
      )}
      {...props}
    >
      {children}
    </ToggleGroupPrimitive.Item>
  );
};

export { ToggleGroup, ToggleGroupItem };
