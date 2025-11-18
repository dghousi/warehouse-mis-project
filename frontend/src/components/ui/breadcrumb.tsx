import * as React from 'react';
import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { cn } from '@/lib/utilities';
import { Slot } from '@radix-ui/react-slot';
import { ChevronRight, MoreHorizontal } from 'lucide-react';

const Breadcrumb = (props: React.ComponentProps<'nav'>): JSX.Element => {
  return <nav aria-label="breadcrumb" data-slot="breadcrumb" {...props} />;
};

const BreadcrumbList = ({ className, ...props }: React.ComponentProps<'ol'>): JSX.Element => {
  return (
    <ol
      data-slot="breadcrumb-list"
      className={cn(
        'text-muted-foreground flex flex-wrap items-center gap-1.5 text-sm break-words sm:gap-2.5',
        className
      )}
      {...props}
    />
  );
};

const BreadcrumbItem = ({ className, ...props }: React.ComponentProps<'li'>): JSX.Element => {
  return (
    <li
      data-slot="breadcrumb-item"
      className={cn('inline-flex items-center gap-1.5', className)}
      {...props}
    />
  );
};

const BreadcrumbLink = ({
  asChild,
  className,
  ...props
}: React.ComponentProps<'a'> & {
  asChild?: boolean;
}): JSX.Element => {
  const Comp = asChild ? Slot : 'a';

  return (
    <Comp
      data-slot="breadcrumb-link"
      className={cn('hover:text-foreground transition-colors', className)}
      {...props}
    />
  );
};

const BreadcrumbPage = ({ className, ...props }: React.ComponentProps<'span'>): JSX.Element => {
  return (
    <span
      data-slot="breadcrumb-page"
      aria-current="page"
      className={cn('text-foreground font-normal', className)}
      {...props}
    />
  );
};

const BreadcrumbSeparator = ({
  children,
  className,
  ...props
}: React.ComponentProps<'li'>): JSX.Element => {
  return (
    <li data-slot="breadcrumb-separator" className={cn('[&>svg]:size-3.5', className)} {...props}>
      {children ?? <ChevronRight />}
    </li>
  );
};

const BreadcrumbEllipsis = ({ className, ...props }: React.ComponentProps<'span'>): JSX.Element => {
  const t = useTranslations('global');
  return (
    <span
      data-slot="breadcrumb-ellipsis"
      className={cn('flex size-9 items-center justify-center', className)}
      {...props}
    >
      <MoreHorizontal aria-hidden="true" focusable="false" className="size-4" />
      <span className="sr-only">{t('more')}</span>
    </span>
  );
};

export {
  Breadcrumb,
  BreadcrumbEllipsis,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
};
