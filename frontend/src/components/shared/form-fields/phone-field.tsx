'use client';

import { JSX } from 'react';
import { MaskInput } from '@/components/ui/mask-input';
import { Controller, useFormContext } from 'react-hook-form';

type Props = {
  name: string;
  validation?: any;
  error?: boolean;
};

export const PhoneField = ({ error, name, validation }: Props): JSX.Element => {
  const { control } = useFormContext();
  return (
    <Controller
      name={name}
      control={control}
      rules={validation}
      render={({ field }) => (
        <MaskInput
          id={name}
          mask="phone"
          value={field.value}
          onValueChange={field.onChange}
          className={error ? 'border-red-500' : ''}
        />
      )}
    />
  );
};
