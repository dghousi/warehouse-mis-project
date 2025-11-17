'use client';

import React from 'react';
import { useTranslations } from 'next-intl';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  useSidebar,
} from '@/components/ui/sidebar';
import { useAuth } from '@/modules/auth/presentation/hooks/useAuth';
import { BadgeCheck, Bell, ChevronsUpDown, LogOut, User } from 'lucide-react';

export const NavUser = React.memo(function NavUser({
  user,
}: Readonly<{
  user: {
    firstName: string;
    lastName: string;
    email: string;
    profilePhotoPath: string;
  };
}>) {
  const { isMobile } = useSidebar();
  const { isLoggingOut, logout } = useAuth();
  const t = useTranslations('sidebar');

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <DropdownMenu>
          <DropdownMenuTrigger asChild>
            <SidebarMenuButton
              size="lg"
              className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
            >
              <Avatar className="h-8 w-8 rounded-lg">
                <AvatarImage src={user.profilePhotoPath} alt={user.firstName} />
                <AvatarFallback className="rounded-lg">
                  <User className="h-6 w-6 text-muted-foreground" />
                </AvatarFallback>
              </Avatar>
              <div className="grid flex-1 text-start text-sm leading-tight">
                <span className="truncate font-medium">
                  {user.firstName} {user.lastName}
                </span>
                <span className="truncate text-xs">{user.email}</span>
              </div>
              <ChevronsUpDown className="ms-auto size-4" />
            </SidebarMenuButton>
          </DropdownMenuTrigger>
          <DropdownMenuContent
            className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
            side={isMobile ? 'bottom' : 'right'}
            align="end"
            sideOffset={4}
          >
            <DropdownMenuGroup>
              <DropdownMenuItem>
                <BadgeCheck />
                {t('navUser.account', { defaultValue: 'Account' })}
              </DropdownMenuItem>
              <DropdownMenuItem>
                <Bell />
                {t('navUser.notifications', { defaultValue: 'Notifications' })}
              </DropdownMenuItem>
            </DropdownMenuGroup>
            <DropdownMenuSeparator />
            <DropdownMenuItem
              className="cursor-pointer"
              onClick={() => {
                logout();
              }}
              disabled={isLoggingOut}
            >
              <LogOut />
              {isLoggingOut
                ? t('navUser.loggingOut', { defaultValue: 'Logging Out...' })
                : t('navUser.logout', { defaultValue: 'Log out' })}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
      </SidebarMenuItem>
    </SidebarMenu>
  );
});
