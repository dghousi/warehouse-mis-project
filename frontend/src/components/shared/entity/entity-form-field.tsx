import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { getErrorMessage } from '@/modules/common/utils/get-error-message';
import { EntityModuleConfig, FormField } from '@/types/EntityModuleConfig';
import { useFormContext } from 'react-hook-form';
import { CustomFieldRenderer } from '../custom-field-renderer';
import { DateField } from '../form-fields/date-field';
import { FileField } from '../form-fields/file-field';
import { InputField } from '../form-fields/input-field';
import { PhoneField } from '../form-fields/phone-field';
import { SelectField } from '../form-fields/select-field';
import { SwitchField } from '../form-fields/switch-field';
import { TextAreaField } from '../form-fields/text-area-field';

export const EntityFormField = ({
  config,
  field,
}: {
  config: EntityModuleConfig;
  field: FormField;
}): JSX.Element => {
  const t = useTranslations(config.singleQueryKey);
  const tGlobal = useTranslations('global');
  const { control, formState } = useFormContext();
  const formErrors = formState.errors;

  const errorMessage = getErrorMessage(formErrors[field.name], t);
  const fieldType = field.type ?? 'text';

  if (field.type === 'custom' && field.render) {
    return <CustomFieldRenderer field={field} control={control} errorMessage={errorMessage} />;
  }

  const fieldProps = {
    config,
    control,
    error: !!formErrors[field.name],
    label: field.label,
    name: field.name,
    options: field.options || [],
    type: fieldType,
    validation: field.validation,
  };

  return (
    <div className="space-y-2">
      {field.type !== 'switch' && (
        <label htmlFor={field.name} className="text-sm font-medium">
          {t(field.label)}
          {!field.required && (
            <span className="text-gray-500 italic ms-1">({tGlobal('optional')})</span>
          )}
        </label>
      )}

      {fieldType === 'select' && <SelectField {...fieldProps} />}
      {fieldType === 'textarea' && <TextAreaField {...fieldProps} />}
      {fieldType === 'phone' && <PhoneField {...fieldProps} />}
      {fieldType === 'date' && <DateField {...fieldProps} />}
      {fieldType === 'file' && <FileField {...fieldProps} />}
      {fieldType === 'switch' && <SwitchField {...fieldProps} />}
      {(fieldType === 'text' ||
        fieldType === 'number' ||
        fieldType === 'email' ||
        fieldType === 'password') && <InputField {...fieldProps} />}

      {errorMessage && <p className="text-red-500 text-sm">{errorMessage}</p>}
    </div>
  );
};
