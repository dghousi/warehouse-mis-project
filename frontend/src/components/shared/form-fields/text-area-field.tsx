'use client';

import { JSX } from 'react';
import { EntityModuleConfig } from '@/types/EntityModuleConfig';
import { useFormContext } from 'react-hook-form';

type Props = {
  name: string;
  rows?: number;
  validation?: any;
  error?: boolean;
  config: EntityModuleConfig;
};

export const TextAreaField = ({ error, name, rows = 4, validation }: Props): JSX.Element => {
  const { register } = useFormContext();

  return (
    <textarea
      {...register(name, validation)}
      id={name}
      rows={rows}
      className={`block w-full rounded-md border p-2 ${error ? 'border-red-500' : 'border-gray-300'}`}
    />
  );
};
