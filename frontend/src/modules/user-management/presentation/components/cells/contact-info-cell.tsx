'use client';

import React, { JSX } from 'react';
import { User } from '@/modules/common/domain/entities/User';
import { PhoneIcon } from 'lucide-react';
import { FaWhatsapp } from 'react-icons/fa';

type Props = {
  user: User;
};

export const ContactInfoCell = ({ user }: Props): JSX.Element => {
  const showContact = !!user.contactNumber;
  const showWhatsapp = !!user.whatsappNumber;

  return (
    <div className="flex flex-col gap-1">
      {showContact && (
        <div className="flex items-center text-gray-900">
          <PhoneIcon className="size-4 text-gray-500 me-2" />
          {user.contactNumber}
        </div>
      )}
      {showWhatsapp && (
        <div className={`flex items-center ${showContact ? 'text-gray-500' : 'text-gray-900'}`}>
          <FaWhatsapp className="size-4 text-gray-500 me-2" />
          {user.whatsappNumber}
        </div>
      )}
    </div>
  );
};
