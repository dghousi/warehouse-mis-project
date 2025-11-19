'use client';

import * as React from 'react';
import { useTranslations } from 'next-intl';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { TeamSwitcher } from '@/components/team-switcher';
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarRail,
} from '@/components/ui/sidebar';
import { useAuthStore } from '@/modules/auth/application/stores/auth-store';
import { BookOpen, GalleryVerticalEnd, Users } from 'lucide-react';

const navItems = [
  {
    icon: Users,
    isActive: true,
    items: [
      { key: 'users', url: '/user-management/users' },
      { key: 'roles', url: '/user-management/roles' },
      { key: 'permissions', url: '/user-management/permissions' },
    ],
    key: 'userManagement',
    url: '/user-management',
  },
  {
    icon: BookOpen,
    items: [
      { key: 'users', url: '#' },
      { key: 'permissions', url: '#' },
      { key: 'settings', url: '#' },
    ],
    key: 'documentation',
    url: '/docs',
  },
];

export const AppSidebar = (props: React.ComponentProps<typeof Sidebar>): React.JSX.Element => {
  const user = useAuthStore((state) => state.user);
  const t = useTranslations('sidebar');
  const localizedNavItems = navItems.map((item) => ({
    ...item,
    items: item.items?.map((subItem) => ({
      ...subItem,
      title: t(`${item.key}.${subItem.key}`, { defaultValue: subItem.key }),
    })),
    title: t(`${item.key}.title`, { defaultValue: item.key }),
  }));

  return (
    <Sidebar collapsible="icon" className="border-e-0 border-s border-s-border" {...props}>
      <SidebarHeader>
        <TeamSwitcher
          teams={[
            {
              logo: GalleryVerticalEnd,
              name: t('name', { defaultValue: 'PaamTech' }),
              plan: 'Enterprise',
            },
          ]}
        />
      </SidebarHeader>
      <SidebarContent>
        <NavMain items={localizedNavItems} />
      </SidebarContent>
      <SidebarFooter>{user && <NavUser user={user} />}</SidebarFooter>
      <SidebarRail />
    </Sidebar>
  );
};
