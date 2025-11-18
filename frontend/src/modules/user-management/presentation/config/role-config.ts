import React from 'react';
import { type EntityModuleConfig } from '@/types/EntityModuleConfig';
import { PermissionsCell } from '../components/cells/permissions-cell';

export const roleConfig: EntityModuleConfig = {
  columns: [
    {
      accessorKey: 'name',
      header: 'config.form.name.label',
    },
    {
      accessorKey: 'displayName',
      header: 'config.table.displayName',
    },
    {
      accessorKey: 'permissions',
      cell: (roles) => React.createElement(PermissionsCell, { permissions: roles.permissions }),
      header: 'config.table.permissions',
    },
  ],
  createButton: 'config.table.add',
  createSubmitText: 'config.create',
  createTitle: 'config.table.create',
  editButton: 'config.table.update',
  editSubmitText: 'config.update',
  editTitle: 'config.table.edit',
  entity: 'config.entity',
  filters: [],
  formFields: [
    {
      label: 'config.form.name.label',
      name: 'name',
      required: true,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.name.validation.maxLength', value: 255 },
        required: 'config.form.name.validation.required',
      },
    },
    {
      label: 'config.form.displayNameEn.label',
      name: 'displayNameEn',
      required: false,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.displayNameEn.validation.maxLength', value: 255 },
      },
    },
    {
      label: 'config.form.displayNamePs.label',
      name: 'displayNamePs',
      required: false,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.displayNamePs.validation.maxLength', value: 255 },
      },
    },
    {
      label: 'config.form.displayNameDr.label',
      name: 'displayNameDr',
      required: false,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.displayNameDr.validation.maxLength', value: 255 },
      },
    },
  ],
  queryKey: 'roles',
  relations: ['permissions'],
  searchFields: [{ label: 'config.table.displayName', value: 'displayName' }],
  singleQueryKey: 'role',
  title: 'config.title',
};
