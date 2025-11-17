'use client';

import React, { JSX } from 'react';
import Image from 'next/image';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import type { User } from '@/modules/common/domain/entities/User';
import { User as UserIcon } from 'lucide-react';

type Props = Readonly<{
  user: User;
}>;

export const PersonalInfoCell = ({ user }: Props): JSX.Element => {
  const fullName = `${user.firstName ?? ''} ${user.lastName ?? ''}`.trim();

  return (
    <div className="flex items-center">
      <div className="size-11 shrink-0 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
        {user.profilePhotoPath &&
        typeof user.profilePhotoPath === 'string' &&
        user.profilePhotoPath !== '' ? (
          <Image
            alt={fullName}
            src={user.profilePhotoPath}
            width={44}
            height={44}
            className="size-11 rounded-full object-cover"
          />
        ) : (
          <UserIcon className="size-6 text-gray-400" />
        )}
      </div>

      <div className="ms-4">
        <div className="flex items-center gap-2 font-medium text-gray-900">
          {!user.enabled && (
            <Tooltip>
              <TooltipTrigger asChild>
                <div className="flex-none rounded-full bg-rose-600/10 p-1 text-rose-600 cursor-default">
                  <div className="size-1.5 rounded-full bg-current" />
                </div>
              </TooltipTrigger>
              <TooltipContent>
                <p>User is inactive</p>
              </TooltipContent>
            </Tooltip>
          )}
          {fullName}
        </div>

        {user.email && <div className="mt-1 text-gray-500">{user.email}</div>}
      </div>
    </div>
  );
};
