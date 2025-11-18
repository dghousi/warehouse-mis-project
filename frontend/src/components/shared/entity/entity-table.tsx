'use client';

import { useState } from 'react';
import { useTranslations } from 'next-intl';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import {
  Table as ShadcnTable,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { type ApiResponse, type PaginationLinks, type PaginationMeta } from '@/types/ApiResponse';
import { TableColumn, type EntityModuleConfig } from '@/types/EntityModuleConfig';
import { flexRender, type Table } from '@tanstack/react-table';
import { AlertTriangle, Edit, PlusCircle, Trash2 } from 'lucide-react';
import { EntityTablePagination } from './entity-table-pagination';

type User = {
  id: string | number;
  name?: string;
  email?: string;
  roles?: Array<string | { name: string }>;
  department?: { name: string };
  [key: string]: any;
};

type EntityTableProps<TData extends { id: string | number }> = {
  table: Table<TData>;
  config: Omit<EntityModuleConfig, 'columns'> & { columns: TableColumn[] };
  isFetching?: boolean;
  error?: any;
  onEdit: (row: { original: TData }) => void;
  onDelete: (id: string | number) => Promise<ApiResponse<null>>;
  pagination?: PaginationMeta;
  links?: PaginationLinks;
  onPageChange: (pageIndex: number) => void;
  onPageSizeChange: (pageSize: number) => void;
  perPage: number;
  refetch?: () => void;
};

export const EntityTable = <TData extends User>({
  config,
  error,
  isFetching,
  onDelete,
  onEdit,
  onPageChange,
  onPageSizeChange,
  pagination,
  perPage,
  refetch,
  table,
}: Readonly<EntityTableProps<TData>>): React.ReactElement => {
  const t = useTranslations(config.singleQueryKey);
  const tGlobal = useTranslations('global');
  const totalItems = pagination?.total ?? table.getRowModel().rows.length;
  const pageIndex = table.getState().pagination.pageIndex;
  const pageSize = pagination?.perPage ?? perPage;
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [selectedId, setSelectedId] = useState<string | number | null>(null);

  const handleDeleteClick = (id: string | number): void => {
    setSelectedId(id);
    setIsDialogOpen(true);
  };

  const confirmDelete = async (): Promise<void> => {
    if (selectedId !== null) {
      await onDelete(selectedId);
      setIsDialogOpen(false);
      setSelectedId(null);
    }
  };

  const renderCellValue = (value: any): string => {
    if (value === null || value === undefined) return '';
    if (Array.isArray(value)) {
      return value
        .map((item) =>
          typeof item === 'object' && item !== null && 'name' in item
            ? (item as { name: string }).name
            : String(item)
        )
        .join(', ');
    }
    if (typeof value === 'object' && value !== null && 'name' in value) {
      return (value as { name: string }).name;
    }
    return String(value);
  };

  if (isFetching) {
    return (
      <div className="rounded-md border">
        <ShadcnTable>
          <TableHeader className="bg-muted">
            <TableRow>
              {config.columns.map((column) => (
                <TableHead key={String(column.accessorKey)}>
                  <Skeleton className="h-6 w-3/4" />
                </TableHead>
              ))}
              <TableHead className="w-[100px]">
                <Skeleton className="h-6 w-3/4" />
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {Array.from({ length: 10 }).map((_, index) => (
              <TableRow
                key={`skeleton-row-${index}-${config.columns[0]?.accessorKey || 'default'}`}
              >
                {config.columns.map((column) => (
                  <TableCell key={`skeleton-cell-${column.accessorKey}`}>
                    <Skeleton className="h-6 w-full" />
                  </TableCell>
                ))}
                <TableCell>
                  <div className="flex space-x-2">
                    <Skeleton className="h-8 w-8" />
                    <Skeleton className="h-8 w-8" />
                  </div>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </ShadcnTable>
      </div>
    );
  }

  if (error) {
    return (
      <div className="flex flex-col items-center justify-center rounded-md border border-red-200 bg-red-50 p-6 text-center">
        <AlertTriangle className="h-12 w-12 text-red-500 mb-4" />
        <h3 className="text-lg font-semibold text-red-700">{tGlobal('errorLoadingData')}</h3>
        <p className="text-sm text-red-600 mt-2">{error.message || tGlobal('unexpectedError')}</p>
        {refetch && (
          <Button
            variant="outline"
            className="mt-4 border-red-500 text-red-500 hover:bg-red-100"
            onClick={refetch}
          >
            {tGlobal('retry')}
          </Button>
        )}
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4">
      <div className="rounded-md border">
        <ShadcnTable>
          <TableHeader className="bg-muted sticky top-0 z-10">
            {table.getHeaderGroups().map((headerGroup) => (
              <TableRow key={headerGroup.id}>
                {headerGroup.headers.map((header) => {
                  if ((header.column.columnDef.meta as { hidden?: boolean })?.hidden) return null;
                  const headerValue =
                    typeof header.column.columnDef.header === 'string'
                      ? t(header.column.columnDef.header, {
                          defaultMessage: header.column.columnDef.header,
                        })
                      : (header.column.columnDef.header ?? '');
                  return (
                    <TableHead key={header.id}>
                      {header.isPlaceholder ? null : flexRender(headerValue, header.getContext())}
                    </TableHead>
                  );
                })}
                <TableHead className="w-[100px] text-center">{tGlobal('actions')}</TableHead>
              </TableRow>
            ))}
          </TableHeader>
          <TableBody>
            {table.getRowModel().rows.length === 0 ? (
              <TableRow>
                <TableCell colSpan={config.columns.length + 1} className="h-24 text-center">
                  <div className="flex flex-col items-center justify-center py-8">
                    <PlusCircle className="h-12 w-12 text-gray-400" />
                    <h3 className="mt-2 text-sm font-semibold text-gray-900">
                      {tGlobal('noRecordsFound')}
                    </h3>
                    <p className="mt-1 text-sm text-gray-500">
                      {tGlobal('getStartedByCreatingANew')} {t(config.entity)}.
                    </p>
                  </div>
                </TableCell>
              </TableRow>
            ) : (
              table.getRowModel().rows.map((row) => (
                <TableRow key={row.id}>
                  {row.getVisibleCells().map((cell) => {
                    const column = config.columns.find((c) => c.accessorKey === cell.column.id);
                    if (column?.meta?.hidden) return null;
                    const ucFirst = (str: string | number): string => {
                      const strVal = String(str);
                      return strVal.charAt(0).toUpperCase() + strVal.slice(1);
                    };
                    const cellValue = column?.cell
                      ? column.cell(row.original)
                      : ucFirst(renderCellValue(cell.getValue()));

                    return <TableCell key={cell.id}>{cellValue}</TableCell>;
                  })}
                  <TableCell className="flex space-x-2">
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => {
                        onEdit({ original: row.original });
                      }}
                      aria-label={`${tGlobal('edit')} ${t(config.entity)}`}
                      className="text-blue-600 hover:text-blue-800 hover:bg-blue-50"
                    >
                      <Edit className="h-4 w-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="icon"
                      onClick={() => {
                        handleDeleteClick(row.original.id);
                      }}
                      aria-label={`${tGlobal('delete')} ${t(config.entity)}`}
                      className="text-red-600 hover:text-red-800 hover:bg-red-50"
                    >
                      <Trash2 className="h-4 w-4" />
                    </Button>
                  </TableCell>
                </TableRow>
              ))
            )}
          </TableBody>
        </ShadcnTable>
      </div>
      <AlertDialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
        <AlertDialogContent>
          <AlertDialogHeader>
            <AlertDialogTitle>{tGlobal('confirmDeleteTitle')}</AlertDialogTitle>
            <AlertDialogDescription>
              {tGlobal('confirmDeleteDescription', { entity: t(config.entity) })}
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel>{tGlobal('cancel')}</AlertDialogCancel>
            <AlertDialogAction
              onClick={confirmDelete}
              className="bg-red-600 hover:bg-red-500 text-white"
            >
              {tGlobal('delete')}
            </AlertDialogAction>
          </AlertDialogFooter>
        </AlertDialogContent>
      </AlertDialog>
      {totalItems > 10 && (
        <EntityTablePagination
          table={table}
          totalItems={totalItems}
          pageIndex={pageIndex}
          pageSize={pageSize}
          perPage={perPage}
          onPageChange={onPageChange}
          onPageSizeChange={onPageSizeChange}
        />
      )}
    </div>
  );
};
