'use client';

import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { EntityModuleConfig } from '@/types/EntityModuleConfig';
import { Controller, RegisterOptions, useFormContext } from 'react-hook-form';

type Props = {
  name: string;
  validation?: RegisterOptions;
  error?: boolean;
  config: EntityModuleConfig;
  label?: string;
};

export const SwitchField = ({
  config,
  error,
  label = '',
  name,
  validation,
}: Props): JSX.Element => {
  const { control } = useFormContext();
  const t = useTranslations(config.singleQueryKey);

  return (
    <Controller
      name={name}
      control={control}
      rules={validation}
      render={({ field }) => (
        <Label htmlFor={name} className="flex items-center space-x-2 cursor-pointer w-full">
          <Switch
            id={name}
            checked={field.value ?? false}
            onCheckedChange={field.onChange}
            aria-invalid={error}
          />
          {label && <span className="text-sm">{t(label)}</span>}
        </Label>
      )}
    />
  );
};
