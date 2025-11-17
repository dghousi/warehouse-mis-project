'use client';

import React, { JSX } from 'react';
import { LocaleSwitcher } from '@/components/locale-switcher';
import {
  Breadcrumb,
  BreadcrumbItem,
  BreadcrumbLink,
  BreadcrumbList,
  BreadcrumbPage,
  BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import { Separator } from '@/components/ui/separator';
import { SidebarTrigger } from '@/components/ui/sidebar';

type BreadcrumbItemType = {
  id: string;
  label: string;
  href?: string;
  isCurrent?: boolean;
  hideOnMobile?: boolean;
};

type HeaderProps = {
  breadcrumbs: BreadcrumbItemType[];
  showLocaleSwitcher?: boolean;
};

export const Header = ({
  breadcrumbs,
  showLocaleSwitcher = true,
}: Readonly<HeaderProps>): JSX.Element => {
  return (
    <header className="flex h-16 shrink-0 items-center justify-between gap-2 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12">
      <div className="flex items-center gap-2 px-4">
        <SidebarTrigger className="-ms-1" />
        <Separator orientation="vertical" className="me-2 data-[orientation=vertical]:h-4" />
        <Breadcrumb>
          <BreadcrumbList>
            {breadcrumbs.map((item, index) => {
              let content: React.ReactNode;

              if (item.isCurrent) {
                content = <BreadcrumbPage>{item.label}</BreadcrumbPage>;
              } else if (item.href) {
                content = <BreadcrumbLink href={item.href}>{item.label}</BreadcrumbLink>;
              } else {
                content = item.label;
              }

              return [
                <BreadcrumbItem
                  key={`${item.id}-item`}
                  className={item.hideOnMobile ? 'hidden md:block' : ''}
                >
                  {content}
                </BreadcrumbItem>,
                index < breadcrumbs.length - 1 && (
                  <BreadcrumbSeparator
                    key={`${item.id}-sep`}
                    className={item.hideOnMobile ? 'hidden md:block' : ''}
                  />
                ),
              ];
            })}
          </BreadcrumbList>
        </Breadcrumb>
      </div>
      {showLocaleSwitcher && (
        <div className="px-4">
          <LocaleSwitcher />
        </div>
      )}
    </header>
  );
};
