'use client';

import React, { JSX } from 'react';
import { Badge } from '@/components/ui/badge';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

type Permission = {
  id?: string | number;
  name?: string;
  displayName?: string;
};

type Props = {
  permissions?: Permission[];
};

export const PermissionsCell = ({ permissions }: Props): JSX.Element | null => {
  if (!permissions || permissions.length === 0) return null;

  const [firstPermission, ...restPermissions] = permissions;

  return (
    <div className="flex items-center gap-2 flex-wrap">
      <Badge variant="secondary">
        {firstPermission.displayName ?? firstPermission.name ?? 'Permission'}
      </Badge>

      {restPermissions.length > 0 && (
        <Popover>
          <PopoverTrigger asChild>
            <Badge className="cursor-pointer" variant="outline">
              +{restPermissions.length}
            </Badge>
          </PopoverTrigger>
          <PopoverContent className="max-w-xs max-h-60 overflow-auto space-y-1 z-50">
            {restPermissions.map((perm, index) => (
              <Badge key={perm.id ?? index} variant="secondary" className="block whitespace-nowrap">
                {perm.displayName ?? perm.name ?? `Permission ${index + 2}`}
              </Badge>
            ))}
          </PopoverContent>
        </Popover>
      )}
    </div>
  );
};
