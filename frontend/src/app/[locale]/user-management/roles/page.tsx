'use client';

import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { Header } from '@/components/header';
import { EntityListClient as EntityList } from '@/components/shared/entity/entity-list';
import { roleConfig } from '@/modules/user-management/presentation/config/role-config';
import {
  useCreateRole,
  useDeleteRole,
  useRoleList,
  useUpdateRole,
} from '@/modules/user-management/presentation/hooks';

// eslint-disable-next-line react/function-component-definition
export default function RolesPage(): JSX.Element {
  const t = useTranslations('role');
  const tSidebar = useTranslations('sidebar.userManagement');
  return (
    <>
      <Header
        breadcrumbs={[
          { id: 'user-management', label: tSidebar('title') },
          { id: 'roles', isCurrent: true, label: t(roleConfig.title) },
        ]}
      />
      <div className="flex flex-1 flex-col">
        <div className="@container/main flex flex-1 flex-col gap-2">
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 lg:px-6">
            <EntityList
              config={roleConfig}
              fetchHook={useRoleList}
              useCreate={useCreateRole}
              useUpdate={useUpdateRole}
              useDelete={useDeleteRole}
            />
          </div>
        </div>
      </div>
    </>
  );
}
