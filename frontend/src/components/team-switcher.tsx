'use client';

import * as React from 'react';
import { JSX } from 'react';
import Image from 'next/image';
import { useRouter } from 'next/navigation';
import { SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { LucideIcon } from 'lucide-react';

export const TeamSwitcher = ({
  teams,
}: Readonly<{ teams: { name: string; logo: LucideIcon; plan: string }[] }>): JSX.Element | null => {
  const [activeTeam] = React.useState(teams[0]);
  const router = useRouter();

  const handleClick = React.useCallback(() => {
    router.push('/');
  }, [router]);

  if (!activeTeam) {
    return null;
  }

  return (
    <SidebarMenu>
      <SidebarMenuItem>
        <SidebarMenuButton
          size="lg"
          onClick={handleClick}
          className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground flex items-center justify-center h-[50px] py-2 relative"
        >
          <Image
            src="/paamtech.png"
            alt="PaamTech Logo"
            width={100}
            height={100}
            priority
            sizes="34"
            className="object-contain dark:brightness-[0.2] dark:grayscale"
          />
        </SidebarMenuButton>
      </SidebarMenuItem>
    </SidebarMenu>
  );
};
