'use client';

import { memo, useCallback, useEffect, useMemo, useState } from 'react';
import { useTranslations } from 'next-intl';
import { useRouter, useSearchParams } from 'next/navigation';
import { type PaginationLinks, type PaginationMeta } from '@/types/ApiResponse';
import { type EntityModuleConfig } from '@/types/EntityModuleConfig';
import { useQueryClient } from '@tanstack/react-query';
import { getCoreRowModel, useReactTable } from '@tanstack/react-table';
import { toast } from 'sonner';
import { useDebounce } from 'use-debounce';
import { EntityDrawer } from './entity-drawer';
import { EntityTable } from './entity-table';
import { EntityTableToolbar } from './entity-table-toolbar';

type EntityListClientProps<TData extends { id: string | number }> = {
  config: EntityModuleConfig;
  fetchHook: (
    page: number,
    perPage: number,
    search: string,
    searchFields: string[],
    relations: string[],
    filters: Record<string, string[]>
  ) => {
    entities: TData[];
    pagination?: PaginationMeta;
    links?: PaginationLinks;
    isFetching: boolean;
    error: any;
    refetch: () => Promise<void>;
  };
  useCreate: () => { mutateAsync: (payload: any) => Promise<any> };
  useUpdate: () => { mutateAsync: (payload: { id: string | number; data: any }) => Promise<any> };
  useDelete: () => { mutateAsync: (id: string | number) => Promise<any> };
};

const EntityList = <TData extends { id: string | number }>({
  config,
  fetchHook,
  useCreate,
  useDelete,
  useUpdate,
}: Readonly<EntityListClientProps<TData>>): React.ReactElement => {
  const t = useTranslations(config.singleQueryKey);
  const queryClient = useQueryClient();
  const searchParameters = useSearchParams();
  const router = useRouter();

  const [drawerOpen, setDrawerOpen] = useState(false);
  const [editingEntity, setEditingEntity] = useState<TData | null>(null);
  const [search, setSearch] = useState(searchParameters.get('search') || '');
  const [selectedSearchFields, setSelectedSearchFields] = useState<string[]>(
    config.searchFields?.map((field) => field.value)
  );

  const [filters, setFilters] = useState<Record<string, string[]>>(() => {
    const initialFilters: Record<string, string[]> = {};
    if (config.filters) {
      for (const filter of config.filters) {
        const values = searchParameters.getAll(`filters[${filter.key}][]`);
        if (values.length > 0) {
          initialFilters[filter.key] = values;
        }
      }
    }
    return initialFilters;
  });

  const [perPage, setPerPage] = useState(Number(searchParameters.get('perPage')) || 10);
  const [formErrors, setFormErrors] = useState<Record<string, string[]> | null>(null);

  const createMutation = useCreate();
  const updateMutation = useUpdate();
  const deleteMutation = useDelete();

  const pageIndex = useMemo(
    () => Math.max(Number(searchParameters.get('page') || 1) - 1, 0),
    [searchParameters]
  );

  const [debouncedSearch] = useDebounce(search, 400);
  const [debouncedSearchFields] = useDebounce(selectedSearchFields, 400);
  const [debouncedFilters] = useDebounce(filters, 400);
  const [debouncedPageIndex] = useDebounce(pageIndex, 400);
  const [debouncedPerPage] = useDebounce(perPage, 400);

  const { entities, error, isFetching, links, pagination, refetch } = fetchHook(
    debouncedPageIndex + 1,
    debouncedPerPage,
    debouncedSearch,
    debouncedSearchFields,
    config.relations || [],
    debouncedFilters
  );

  useEffect(() => {}, [entities, filters]);

  const pageSize = pagination?.perPage ?? perPage;
  const totalItems = pagination?.total ?? entities.length;

  useEffect(() => {
    const parameters = new URLSearchParams();
    parameters.set('page', String(debouncedPageIndex + 1));
    parameters.set('perPage', String(debouncedPerPage));
    if (debouncedSearch) {
      parameters.set('search', debouncedSearch);
    }
    for (const field of debouncedSearchFields) {
      parameters.append('searchFields[]', field);
    }

    for (const relation of config.relations || []) {
      parameters.append('include[]', relation);
    }

    for (const [key, values] of Object.entries(debouncedFilters)) {
      for (const value of values) {
        parameters.append(`filter[${key}][]`, value);
      }
    }

    router.replace(`?${parameters.toString()}`, { scroll: false });
  }, [
    debouncedSearch,
    debouncedSearchFields,
    debouncedFilters,
    debouncedPageIndex,
    debouncedPerPage,
    config.relations,
    router,
  ]);

  const onPageChange = useCallback(
    (newPageIndex: number) => {
      const parameters = new URLSearchParams();
      parameters.set('page', String(newPageIndex + 1));
      parameters.set('perPage', String(perPage));
      if (search) {
        parameters.set('search', search);
      }
      for (const field of selectedSearchFields) {
        parameters.append('searchFields[]', field);
      }

      for (const relation of config.relations || []) {
        parameters.append('include[]', relation);
      }

      for (const [key, values] of Object.entries(filters)) {
        for (const value of values) {
          parameters.append(`filter[${key}][]`, value);
        }
      }

      router.push(`?${parameters.toString()}`, { scroll: false });
    },
    [router, perPage, search, selectedSearchFields, config.relations, filters]
  );

  const onPageSizeChange = useCallback(
    (newPageSize: number) => {
      setPerPage(newPageSize);
      const parameters = new URLSearchParams();
      parameters.set('page', '1');
      parameters.set('perPage', String(newPageSize));
      if (search) {
        parameters.set('search', search);
      }
      for (const field of selectedSearchFields) {
        parameters.append('searchFields[]', field);
      }

      for (const relation of config.relations || []) {
        parameters.append('include[]', relation);
      }

      for (const [key, values] of Object.entries(filters)) {
        for (const value of values) {
          parameters.append(`filter[${key}][]`, value);
        }
      }

      router.push(`?${parameters.toString()}`, { scroll: false });
    },
    [router, search, selectedSearchFields, config.relations, filters]
  );

  const setDrawerOpenCallback = useCallback((open: boolean) => {
    setDrawerOpen(open);
  }, []);

  const setFormErrorsCallback = useCallback((errors: Record<string, string[]> | null) => {
    setFormErrors(errors);
  }, []);

  const table = useReactTable({
    columns: config.columns,
    data: entities,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    onPaginationChange: (updater) => {
      const next = typeof updater === 'function' ? updater({ pageIndex, pageSize }) : updater;
      onPageChange(next.pageIndex);
    },
    pageCount: Math.ceil(totalItems / pageSize),
    state: { pagination: { pageIndex, pageSize } },
  });

  const blurActiveElement = (): void => {
    if (document.activeElement instanceof HTMLElement) {
      document.activeElement.blur();
    }
  };

  const handleEdit = useCallback(
    (row: any) => {
      blurActiveElement();
      setEditingEntity(row.original);
      setDrawerOpenCallback(true);
      setFormErrorsCallback(null);
    },
    [setDrawerOpenCallback, setFormErrorsCallback]
  );

  const handleCreate = useCallback(() => {
    blurActiveElement();
    setEditingEntity(null);
    setDrawerOpenCallback(true);
    setFormErrorsCallback(null);
  }, [setDrawerOpenCallback, setFormErrorsCallback]);

  const showToast = useCallback((message: string, success: boolean) => {
    toast[success ? 'success' : 'error'](message, {
      position: 'top-center',
      style: {
        background: success ? '#22c55e' : '#ef4444',
        border: '1px solid #e5e7eb',
        color: '#ffffff',
      },
    });
  }, []);

  const handleCreateSuccess = useCallback(
    (pageIndex: number, perPage: number) => {
      if (pageIndex !== 0) {
        const targetQueryKey = [config.queryKey, 1, perPage, '', JSON.stringify({})];
        const queryState = queryClient.getQueryState(targetQueryKey);
        if (
          !queryState ||
          queryState.isInvalidated ||
          queryState.dataUpdatedAt < Date.now() - 5 * 60 * 1000
        ) {
          const parameters = new URLSearchParams();
          parameters.set('page', '1');
          parameters.set('perPage', String(perPage));
          router.push(`?${parameters.toString()}`, { scroll: false });
        }
      }
    },
    [config.queryKey, queryClient, router]
  );

  const handleSubmit = useCallback(
    async (data: any, id?: number) => {
      setFormErrorsCallback(null);
      const response = await (id
        ? updateMutation.mutateAsync({ data, id })
        : createMutation.mutateAsync(data));
      const entity = t(config.entity);
      if (response.success) {
        showToast(
          t(id ? 'config.presentation.updateSuccess' : 'config.presentation.createSuccess', {
            entity,
          }),
          true
        );
        if (id) {
          queryClient.invalidateQueries({
            exact: true,
            queryKey: [config.singleQueryKey, id],
          });
        } else {
          handleCreateSuccess(pageIndex, perPage);
        }
        return { success: true };
      }
      if (response.error?.details) {
        setFormErrorsCallback(response.error.details);
      }
      showToast(
        response.error?.message ||
          t(id ? 'config.repository.updateFailed' : 'config.repository.createFailed', {
            entity,
          }),
        false
      );
      return { success: false };
    },
    [
      config.singleQueryKey,
      config.entity,
      createMutation,
      updateMutation,
      pageIndex,
      perPage,
      queryClient,
      setFormErrorsCallback,
      showToast,
      handleCreateSuccess,
      t,
    ]
  );

  const handleDelete = useCallback(
    async (id: string | number) => {
      const response = await deleteMutation.mutateAsync(id);
      const entity = t(config.entity);
      if (response.success) {
        showToast(t('config.presentation.deleteSuccess', { entity }), true);
        queryClient.invalidateQueries({
          exact: true,
          queryKey: [config.singleQueryKey, id],
        });
      } else {
        showToast(t('config.repository.deleteFailed', { entity }), false);
      }
      return response;
    },
    [config.singleQueryKey, config.entity, deleteMutation, queryClient, showToast, t]
  );

  return (
    <div className="space-y-4">
      <EntityTableToolbar
        table={table}
        config={config}
        search={search}
        setSearch={setSearch}
        filters={filters}
        setFilters={setFilters}
        selectedSearchFields={selectedSearchFields}
        setSelectedSearchFields={setSelectedSearchFields}
        drawerTriggerLabel={t(config.createButton, {
          entity: t(config.entity),
        })}
        renderDrawerForm={() => (
          <EntityDrawer
            open={drawerOpen}
            onOpenChange={setDrawerOpenCallback}
            entity={editingEntity}
            config={config}
            errors={formErrors}
            onSubmit={handleSubmit}
          />
        )}
        onTrigger={handleCreate}
      />
      <EntityTable
        table={table}
        config={config}
        isFetching={isFetching}
        error={error}
        onEdit={handleEdit}
        onDelete={handleDelete}
        pagination={pagination}
        links={links}
        onPageChange={onPageChange}
        onPageSizeChange={onPageSizeChange}
        perPage={perPage}
        refetch={refetch}
      />
    </div>
  );
};

export const EntityListClient = memo(EntityList) as typeof EntityList;
