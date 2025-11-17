'use client';

import { JSX, type ComponentProps } from 'react';
import { useTranslations } from 'next-intl';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utilities';
import { Command as CommandPrimitive } from 'cmdk';
import { SearchIcon } from 'lucide-react';

export const Command = ({
  className,
  ...props
}: ComponentProps<typeof CommandPrimitive>): JSX.Element => (
  <CommandPrimitive
    data-slot="command"
    className={cn(
      'bg-popover text-popover-foreground flex h-full w-full flex-col overflow-hidden rounded-md',
      className
    )}
    {...props}
  />
);

export const CommandDialog = ({
  children,
  className,
  description = 'Search for a command to run...',
  showCloseButton = true,
  title = 'Command Palette',
  ...props
}: ComponentProps<typeof Dialog> & {
  title?: string;
  description?: string;
  className?: string;
  showCloseButton?: boolean;
}): JSX.Element => {
  const t = useTranslations('component');
  return (
    <Dialog {...props}>
      <DialogHeader className="sr-only">
        <DialogTitle>{title || t('commandPalette')}</DialogTitle>
        <DialogDescription>{description || t('searchForACommand')}</DialogDescription>
      </DialogHeader>
      <DialogContent
        className={cn('overflow-hidden p-0', className)}
        showCloseButton={showCloseButton}
      >
        <Command className="[&_[cmdk-group-heading]]:text-muted-foreground [&_[cmdk-group-heading]]:px-2 [&_[cmdk-group-heading]]:font-medium [&_[cmdk-group]]:px-2 [&_[cmdk-group]:not([hidden])_~[cmdk-group]]:pt-0 [&_[cmdk-input-wrapper]_svg]:h-5 [&_[cmdk-input-wrapper]_svg]:w-5 [&_[cmdk-input]]:h-12 [&_[cmdk-item]]:px-2 [&_[cmdk-item]]:py-3 [&_[cmdk-item]_svg]:h-5 [&_[cmdk-item]_svg]:w-5">
          {children}
        </Command>
      </DialogContent>
    </Dialog>
  );
};

export const CommandInput = ({
  className,
  ...props
}: ComponentProps<typeof CommandPrimitive.Input>): JSX.Element => {
  const t = useTranslations('component');
  return (
    <div data-slot="command-input-wrapper" className="flex h-9 items-center gap-2 border-b px-3">
      <SearchIcon className="size-4 shrink-0 opacity-50" />
      <CommandPrimitive.Input
        data-slot="command-input"
        placeholder={t('searchForACommand')}
        className={cn(
          'placeholder:text-muted-foreground flex h-10 w-full rounded-md bg-transparent py-3 text-sm outline-none disabled:cursor-not-allowed disabled:opacity-50',
          className
        )}
        {...props}
      />
    </div>
  );
};

export const CommandList = ({
  className,
  ...props
}: ComponentProps<typeof CommandPrimitive.List>): JSX.Element => (
  <CommandPrimitive.List
    data-slot="command-list"
    className={cn('max-h-[300px] scroll-py-1 overflow-x-hidden overflow-y-auto', className)}
    {...props}
  />
);

export const CommandEmpty = ({
  ...props
}: ComponentProps<typeof CommandPrimitive.Empty>): JSX.Element => (
  <CommandPrimitive.Empty
    data-slot="command-empty"
    className="py-6 text-center text-sm"
    {...props}
  />
);

export const CommandGroup = ({
  className,
  ...props
}: ComponentProps<typeof CommandPrimitive.Group>): JSX.Element => (
  <CommandPrimitive.Group
    data-slot="command-group"
    className={cn(
      'text-foreground [&_[cmdk-group-heading]]:text-muted-foreground overflow-hidden p-1 [&_[cmdk-group-heading]]:px-2 [&_[cmdk-group-heading]]:py-1.5 [&_[cmdk-group-heading]]:text-xs [&_[cmdk-group-heading]]:font-medium',
      className
    )}
    {...props}
  />
);

export const CommandSeparator = ({
  className,
  ...props
}: ComponentProps<typeof CommandPrimitive.Separator>): JSX.Element => (
  <CommandPrimitive.Separator
    data-slot="command-separator"
    className={cn('bg-border -mx-1 h-px', className)}
    {...props}
  />
);

export const CommandItem = ({
  className,
  ...props
}: ComponentProps<typeof CommandPrimitive.Item>): JSX.Element => (
  <CommandPrimitive.Item
    data-slot="command-item"
    className={cn(
      "data-[selected=true]:bg-accent data-[selected=true]:text-accent-foreground [&_svg:not([class*='text-'])]:text-muted-foreground relative flex cursor-default items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-hidden select-none data-[disabled=true]:pointer-events-none data-[disabled=true]:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
      className
    )}
    {...props}
  />
);

export const CommandShortcut = ({ className, ...props }: ComponentProps<'span'>): JSX.Element => (
  <span
    data-slot="command-shortcut"
    className={cn('text-muted-foreground ms-auto text-xs tracking-widest', className)}
    {...props}
  />
);
