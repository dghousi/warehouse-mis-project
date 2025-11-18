'use client';

import * as React from 'react';
import { JSX } from 'react';
import { cn } from '@/lib/utilities';
import * as PopoverPrimitive from '@radix-ui/react-popover';

const Popover = (
  props: Readonly<React.ComponentProps<typeof PopoverPrimitive.Root>>
): JSX.Element => {
  return <PopoverPrimitive.Root data-slot="popover" {...props} />;
};

const PopoverTrigger = (
  props: React.ComponentProps<typeof PopoverPrimitive.Trigger>
): JSX.Element => {
  return <PopoverPrimitive.Trigger data-slot="popover-trigger" {...props} />;
};

const PopoverContent = ({
  align = 'center',
  children,
  className,
  sideOffset = 4,
  ...props
}: React.ComponentProps<typeof PopoverPrimitive.Content>): JSX.Element => {
  const [usePortal, setUsePortal] = React.useState(true);

  React.useEffect(() => {
    const inDrawer =
      !!document.querySelector('[data-slot="drawer-content"]') ||
      !!document.querySelector('[data-slot="drawer-body"]') ||
      !!document.querySelector('.drawer-content');

    setUsePortal(!inDrawer);
  }, []);

  const content = (
    <PopoverPrimitive.Content
      data-slot="popover-content"
      align={align}
      sideOffset={sideOffset}
      className={cn(
        'z-50 rounded-md border bg-popover text-popover-foreground shadow-md outline-hidden',
        'data-[state=open]:animate-in data-[state=closed]:animate-out',
        'data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0',
        'data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
        'data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-end-2',
        'data-[side=right]:slide-in-from-start-2 data-[side=top]:slide-in-from-bottom-2',
        className
      )}
      {...props}
    >
      {children}
    </PopoverPrimitive.Content>
  );

  return usePortal ? <PopoverPrimitive.Portal>{content}</PopoverPrimitive.Portal> : content;
};

const PopoverAnchor = (
  props: React.ComponentProps<typeof PopoverPrimitive.Anchor>
): JSX.Element => {
  return <PopoverPrimitive.Anchor data-slot="popover-anchor" {...props} />;
};

export { Popover, PopoverAnchor, PopoverContent, PopoverTrigger };
