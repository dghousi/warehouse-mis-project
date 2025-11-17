'use client';

import React, { JSX } from 'react';
import { Badge } from '@/components/ui/badge';
import { User } from '@/modules/common/domain/entities/User';
import { AlertCircleIcon, BadgeCheckIcon, ClockIcon } from 'lucide-react';

type Props = {
  user: User;
};

export const StatusBadgeCell = ({ user }: Props): JSX.Element => {
  const status = typeof user.status === 'string' ? user.status.toLowerCase() : '';
  let icon: React.ReactNode = null;
  let text = '';
  let variant: 'default' | 'secondary' | 'destructive' | 'outline';
  let className = 'flex items-center gap-1 capitalize ';

  switch (status) {
    case 'approved':
      icon = <BadgeCheckIcon className="size-3.5" />;
      text = 'Approved';
      variant = 'default';
      className += 'bg-green-700 text-white';
      break;
    case 'pending':
      icon = <ClockIcon className="size-3.5" />;
      text = 'Pending';
      variant = 'secondary';
      className += 'text-yellow-700';
      break;
    case 'rejected':
      icon = <AlertCircleIcon className="size-3.5" />;
      text = 'Rejected';
      variant = 'destructive';
      break;
    default:
      text = status || 'Unknown';
      variant = 'outline';
  }

  return (
    <Badge className={className} variant={variant}>
      {icon} {text}
    </Badge>
  );
};
