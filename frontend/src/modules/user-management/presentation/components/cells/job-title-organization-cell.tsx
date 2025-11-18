'use client';

import React, { JSX } from 'react';
import { User } from '@/modules/common/domain/entities/User';

type Props = {
  user: User;
};

export const JobTitleOrganizationCell = ({ user }: Props): JSX.Element => {
  return (
    <div className="flex items-center">
      <div>
        <div className="font-medium text-gray-900">{user.jobTitle}</div>
        <div className="mt-1 text-gray-500">{user.mainOrganizationId}</div>
      </div>
    </div>
  );
};
