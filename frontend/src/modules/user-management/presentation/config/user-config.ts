'use client';

import React from 'react';
import { type EntityModuleConfig } from '@/types/EntityModuleConfig';
import { ContactInfoCell } from '../components/cells/contact-info-cell';
import { JobTitleOrganizationCell } from '../components/cells/job-title-organization-cell';
import { PersonalInfoCell } from '../components/cells/personal-info-cell';
import { StatusBadgeCell } from '../components/cells/status-badge-cell';
import { ReportToField } from '../components/fields/report-to-field';

export const userConfig: EntityModuleConfig = {
  columns: [
    {
      accessorKey: 'firstName',
      cell: (user) => React.createElement(PersonalInfoCell, { user }),
      header: 'config.table.personalInfo',
    },
    {
      accessorKey: 'mainOrganizationId',
      cell: (user) => React.createElement(JobTitleOrganizationCell, { user }),
      header: 'config.table.jobTitle/organization',
    },
    {
      accessorKey: 'reportTo',
      header: 'config.table.reportTo',
    },
    {
      accessorKey: 'contactNumber',
      cell: (user) => React.createElement(ContactInfoCell, { user }),
      header: 'config.table.contactInfo',
    },
    {
      accessorKey: 'rights',
      header: 'config.form.rights.label',
    },
    {
      accessorKey: 'status',
      cell: (user) => React.createElement(StatusBadgeCell, { user }),
      header: 'config.form.status.label',
    },
    {
      accessorKey: 'enabled',
      header: 'config.form.enabled.label',
      meta: { hidden: true },
    },
  ],
  createButton: 'config.table.add',
  createSubmitText: 'config.create',
  createTitle: 'config.table.create',
  editButton: 'config.table.update',
  editSubmitText: 'config.update',
  editTitle: 'config.table.edit',
  entity: 'config.entity',
  filters: [
    {
      key: 'rights',
      options: [
        { label: 'config.form.rights.options.create', value: 'create' },
        { label: 'config.form.rights.options.review', value: 'review' },
        { label: 'config.form.rights.options.approval', value: 'approval' },
      ],
      title: 'config.form.rights.label',
    },
    {
      key: 'status',
      options: [
        { label: 'config.form.status.options.pending', value: 'pending' },
        { label: 'config.form.status.options.approved', value: 'approved' },
        { label: 'config.form.status.options.rejected', value: 'rejected' },
        { label: 'config.form.status.options.uploadForm', value: 'uploadForm' },
      ],
      title: 'config.form.status.label',
    },
    {
      key: 'enabled',
      options: [
        { label: 'config.form.enabled.options.active', value: 1 },
        { label: 'config.form.enabled.options.inactive', value: 0 },
      ],
      title: 'config.form.enabled.label',
    },
  ],
  formFields: [
    {
      label: 'config.form.firstName.label',
      name: 'firstName',
      required: true,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.firstName.validation.maxLength', value: 255 },
        required: 'config.form.firstName.validation.required',
      },
    },
    {
      label: 'config.form.lastName.label',
      name: 'lastName',
      required: false,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.lastName.validation.maxLength', value: 255 },
      },
    },
    {
      label: 'config.form.email.label',
      name: 'email',
      required: true,
      type: 'email',
      validation: {
        maxLength: { message: 'config.form.email.validation.maxLength', value: 255 },
        pattern: {
          message: 'config.form.email.validation.email',
          value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        },
        required: 'config.form.email.validation.required',
      },
    },
    {
      label: 'config.form.password.label',
      name: 'password',
      type: 'password',
    },
    {
      label: 'config.form.contactNumber.label',
      name: 'contactNumber',
      required: false,
      type: 'phone',
      validation: {
        maxLength: { message: 'config.form.contactNumber.validation.maxLength', value: 255 },
      },
    },
    {
      label: 'config.form.whatsappNumber.label',
      name: 'whatsappNumber',
      required: false,
      type: 'phone',
      validation: {
        maxLength: { message: 'config.form.whatsappNumber.validation.maxLength', value: 255 },
      },
    },
    {
      label: 'config.form.jobTitle.label',
      name: 'jobTitle',
      required: true,
      type: 'text',
      validation: {
        maxLength: { message: 'config.form.jobTitle.validation.maxLength', value: 255 },
        required: 'config.form.jobTitle.validation.required',
      },
    },
    {
      label: 'config.form.mainOrganizationId.label',
      name: 'mainOrganizationId',
      required: false,
      type: 'number',
      validation: {
        pattern: { message: 'config.form.mainOrganizationId.validation.pattern', value: /^\d+$/ },
      },
    },
    {
      label: 'config.form.reportToId.label',
      name: 'reportToId',
      render: ({ defaultValues, onChange, value }) => {
        return React.createElement(ReportToField, {
          defaultValues,
          onChange,
          value,
        });
      },
      required: false,
      type: 'custom',
    },
    {
      label: 'config.form.rights.label',
      name: 'rights',
      options: [
        {
          label: 'config.form.rights.options.create',
          value: 'create',
        },
        {
          label: 'config.form.rights.options.review',
          value: 'review',
        },
        {
          label: 'config.form.rights.options.approval',
          value: 'approval',
        },
      ],
      required: false,
      type: 'select',
      validation: {
        in: {
          message: 'config.form.rights.validation.in',
          value: ['create', 'review', 'approval'],
        },
      },
    },
    {
      label: 'config.form.status.label',
      name: 'status',
      options: [
        {
          label: 'config.form.status.options.pending',
          value: 'pending',
        },
        {
          label: 'config.form.status.options.approved',
          value: 'approved',
        },
        {
          label: 'config.form.status.options.rejected',
          value: 'rejected',
        },
        {
          label: 'config.form.status.options.uploadForm',
          value: 'uploadForm',
        },
      ],
      required: false,
      type: 'select',
      validation: {
        in: {
          message: 'config.form.status.validation.in',
          value: ['pending', 'approved', 'rejected', 'uploadForm'],
        },
      },
    },
    {
      label: 'config.form.remarks.label',
      name: 'remarks',
      required: false,
      type: 'textarea',
      validation: {
        maxLength: { message: 'config.form.remarks.validation.maxLength', value: 255 },
      },
    },
    {
      label: 'config.form.profilePhotoPath.label',
      name: 'profilePhotoPath',
      required: false,
      type: 'file',
      validation: {
        maxSize: { message: 'config.form.profilePhotoPath.validation.maxSize', value: 5120 },
        mimes: {
          message: 'config.form.profilePhotoPath.validation.mimes',
          value: ['jpg', 'jpeg', 'png'],
        },
      },
    },
    {
      label: 'config.form.userFormPath.label',
      name: 'userFormPath',
      required: false,
      type: 'file',
      validation: {
        maxSize: { message: 'config.form.userFormPath.validation.maxSize', value: 5120 },
        mimes: {
          message: 'config.form.userFormPath.validation.mimes',
          value: ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        },
      },
    },
  ],
  queryKey: 'users',
  relations: ['reportTo', 'roles'],
  searchFields: [
    { label: 'config.form.firstName.label', value: 'firstName' },
    { label: 'config.form.lastName.label', value: 'lastName' },
    { label: 'config.form.email.label', value: 'email' },
    { label: 'config.form.jobTitle.label', value: 'jobTitle' },
  ],
  singleQueryKey: 'user',
  title: 'config.title',
};
