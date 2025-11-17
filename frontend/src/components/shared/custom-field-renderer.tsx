import { JSX } from 'react';
import { FormField } from '@/types/EntityModuleConfig';
import { Control, Controller } from 'react-hook-form';

type CustomFieldRendererProps = {
  field: FormField;
  control: Control<any>;
  errorMessage?: string;
};

export const CustomFieldRenderer = ({
  control,
  errorMessage,
  field,
}: CustomFieldRendererProps): JSX.Element => {
  return (
    <div className="space-y-2">
      <Controller
        name={field.name}
        control={control}
        render={({ field: controllerField }) => (
          <>
            {field.render?.({
              defaultValues: controllerField.value,
              onChange: controllerField.onChange,
              value: controllerField.value,
            })}

            {errorMessage && <p className="text-red-500 text-sm">{errorMessage}</p>}
          </>
        )}
      />
    </div>
  );
};
