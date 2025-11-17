'use client';

import { useLocale } from 'next-intl';
import { usePathname } from 'next/navigation';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
  SidebarGroup,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem,
} from '@/components/ui/sidebar';
import { Link } from '@/i18n/navigation';
import { cn } from '@/lib/utilities';
import { ChevronRight, type LucideIcon } from 'lucide-react';

export const NavMain = ({
  items,
}: {
  readonly items: {
    title: string;
    url: string;
    icon?: LucideIcon;
    items?: {
      title: string;
      url: string;
    }[];
  }[];
}): React.ReactElement => {
  const pathname = usePathname();
  const locale = useLocale();
  const isRtl = ['dr', 'ps'].includes(locale);

  const cleanPathname = pathname.replace(/^\/[a-z]{2}/, '').split('?')[0];

  return (
    <SidebarGroup>
      <SidebarMenu>
        {items.map((item) => {
          const isParentActive =
            pathname === item.url ||
            item.items?.some(
              (subItem) => cleanPathname === subItem.url || cleanPathname.startsWith(subItem.url)
            );

          return (
            <Collapsible
              key={item.title}
              asChild
              defaultOpen={isParentActive}
              className="group/collapsible"
            >
              <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                  <SidebarMenuButton tooltip={item.title}>
                    {item.icon && <item.icon />}
                    <span>{item.title}</span>
                    <ChevronRight
                      className={cn(
                        'ms-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90',
                        isRtl && 'rotate-180'
                      )}
                    />
                  </SidebarMenuButton>
                </CollapsibleTrigger>
                <CollapsibleContent>
                  <SidebarMenuSub>
                    {item.items?.map((subItem) => {
                      const isActive =
                        cleanPathname === subItem.url || cleanPathname.startsWith(subItem.url);

                      return (
                        <SidebarMenuSubItem key={subItem.title}>
                          <SidebarMenuSubButton asChild>
                            <Link
                              href={subItem.url}
                              className={cn(
                                'block px-3 py-2 rounded-md text-sm transition-colors hover:bg-muted',
                                isActive && 'bg-muted text-primary font-medium'
                              )}
                            >
                              {subItem.title}
                            </Link>
                          </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                      );
                    })}
                  </SidebarMenuSub>
                </CollapsibleContent>
              </SidebarMenuItem>
            </Collapsible>
          );
        })}
      </SidebarMenu>
    </SidebarGroup>
  );
};
