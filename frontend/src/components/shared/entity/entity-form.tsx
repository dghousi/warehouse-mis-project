'use client';

import { JSX, useCallback, useEffect } from 'react';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { setServerErrors } from '@/modules/common/utils/set-server-errors';
import { EntityModuleConfig, FormField } from '@/types/EntityModuleConfig';
import { FormProvider, useForm } from 'react-hook-form';
import { EntityFormField } from './entity-form-field';

type EntityFormProps = {
  defaultValues: any;
  formConfig: FormField[];
  config: EntityModuleConfig;
  onSubmit: (data: any) => Promise<{ success: boolean }>;
  onSuccess: () => void;
  submitText: string;
  errors?: Record<string, string[]> | null;
};

export const EntityForm = ({
  config,
  defaultValues,
  errors,
  formConfig,
  onSubmit,
  onSuccess,
  submitText,
}: EntityFormProps): JSX.Element => {
  const tComponent = useTranslations('component');
  const methods = useForm({ defaultValues });

  const {
    formState: { isSubmitting },
    handleSubmit,
    reset,
    setError,
    trigger,
  } = methods;

  useEffect(() => {
    if (errors) setServerErrors(errors, setError);
  }, [errors, setError]);

  useEffect(() => {
    reset(defaultValues);
  }, [defaultValues, reset]);

  const handleFormSubmit = useCallback(
    async (data: any) => {
      if (!(await trigger())) return;
      try {
        const response = await onSubmit(data);
        if (response.success) {
          onSuccess();
          reset();
        }
      } catch (error) {
        console.error('Form submission error:', error);
      }
    },
    [onSubmit, onSuccess, reset, trigger]
  );

  return (
    <FormProvider {...methods}>
      <form onSubmit={handleSubmit(handleFormSubmit)} className="space-y-4 px-4">
        {formConfig.map((field) => (
          <EntityFormField key={field.name} field={field} config={config} />
        ))}

        <Button type="submit" disabled={isSubmitting} className="float-end">
          {isSubmitting ? tComponent('submitting') : submitText}
        </Button>
      </form>
    </FormProvider>
  );
};
