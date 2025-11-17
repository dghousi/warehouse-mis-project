'use client';

import React, { JSX } from 'react';
import { EntityCombobox } from '@/components/shared/entity/entity-combobox';
import { User } from '@/modules/common/domain/entities/User';
import { userConfig } from '../../config/user-config';
import { useGetUser, useUserList } from '../../hooks';

type ReportToFieldProps = {
  value?: string | number;
  onChange: (value: string | number, item?: User) => void;
  defaultValues?: { reportToId?: string | number };
};

export const ReportToField = ({
  defaultValues,
  onChange,
  value,
}: ReportToFieldProps): JSX.Element => {
  const reportToId = defaultValues?.reportToId ?? null;
  const shouldFetchReportTo = !!reportToId && (!value || String(value) === String(reportToId));
  const { data: userData, isFetching } = useGetUser(reportToId || '', {
    enabled: shouldFetchReportTo,
  });

  return (
    <EntityCombobox
      fetchHook={useUserList}
      fetchByIdHook={useGetUser}
      label="Report To"
      value={value ?? reportToId ?? undefined}
      initialItem={userData?.data ?? undefined}
      onChange={onChange}
      valueKey="id"
      config={userConfig}
      labelFormatter={(u: any) => `${u.firstName ?? ''} ${u.lastName ?? ''}`.trim()}
      queryKey={userConfig.singleQueryKey}
      disabled={isFetching}
    />
  );
};
