'use client';

import { JSX } from 'react';
import { MaskInput } from '@/components/ui/mask-input';
import { Controller, RegisterOptions, useFormContext } from 'react-hook-form';

type Props = {
  name: string;
  validation?: RegisterOptions;
  error?: boolean;
};

export const DateField = ({ error, name, validation }: Props): JSX.Element => {
  const { control } = useFormContext();

  return (
    <Controller
      name={name}
      control={control}
      rules={validation}
      render={({ field }) => (
        <MaskInput
          id={name}
          mask="date"
          value={field.value}
          onValueChange={field.onChange}
          className={error ? 'border-red-500' : ''}
        />
      )}
    />
  );
};
