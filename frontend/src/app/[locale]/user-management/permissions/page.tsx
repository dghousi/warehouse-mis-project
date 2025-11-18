'use client';

import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { Header } from '@/components/header';
import { EntityListClient as EntityList } from '@/components/shared/entity/entity-list';
import { permissionConfig } from '@/modules/user-management/presentation/config/permission-config';
import {
  useCreatePermission,
  useDeletePermission,
  usePermissionList,
  useUpdatePermission,
} from '@/modules/user-management/presentation/hooks';

// eslint-disable-next-line react/function-component-definition
export default function PermissionsPage(): JSX.Element {
  const t = useTranslations('user');
  const tSidebar = useTranslations('sidebar.userManagement');
  return (
    <>
      <Header
        breadcrumbs={[
          { id: 'user-management', label: tSidebar('title') },
          { id: 'permissions', isCurrent: true, label: t(permissionConfig.title) },
        ]}
      />
      <div className="flex flex-1 flex-col">
        <div className="@container/main flex flex-1 flex-col gap-2">
          <div className="flex flex-col gap-4 py-4 md:gap-6 md:py-6 px-4 lg:px-6">
            <EntityList
              config={permissionConfig}
              fetchHook={usePermissionList}
              useCreate={useCreatePermission}
              useUpdate={useUpdatePermission}
              useDelete={useDeletePermission}
            />
          </div>
        </div>
      </div>
    </>
  );
}
