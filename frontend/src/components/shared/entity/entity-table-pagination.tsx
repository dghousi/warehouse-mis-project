'use client';

import { JSX } from 'react';
import { useLocale, useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { getDirection } from '@/modules/common/utils/locale';
import { type Table } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';

type EntityTablePaginationProps<TData> = {
  table: Table<TData>;
  totalItems: number;
  pageIndex: number;
  pageSize: number;
  perPage: number;
  onPageChange: (pageIndex: number) => void;
  onPageSizeChange: (pageSize: number) => void;
};

export const EntityTablePagination = <TData,>({
  onPageChange,
  onPageSizeChange,
  pageIndex,
  pageSize,
  perPage,
  table,
  totalItems,
}: EntityTablePaginationProps<TData>): JSX.Element => {
  const t = useTranslations('component.pagination');
  const locale = useLocale();
  const direction = getDirection(locale);

  const totalPages = Math.ceil(totalItems / pageSize);
  const currentPage = pageIndex + 1;
  const start = totalItems === 0 ? 0 : pageIndex * pageSize + 1;
  const end = Math.min(totalItems, (pageIndex + 1) * pageSize);
  const canPrevious = pageIndex > 0;
  const canNext = currentPage < totalPages;

  const FirstIcon = direction === 'rtl' ? ChevronsRight : ChevronsLeft;
  const PrevIcon = direction === 'rtl' ? ChevronRight : ChevronLeft;
  const NextIcon = direction === 'rtl' ? ChevronLeft : ChevronRight;
  const LastIcon = direction === 'rtl' ? ChevronsLeft : ChevronsRight;

  return (
    <div className="flex items-center justify-between px-2">
      <div className="text-muted-foreground flex-1 text-sm">
        {t('showing')} {start} {t('to')} {end} {t('of')} {totalItems} {t('results')}
      </div>
      <div className="flex items-center space-x-6 lg:space-x-8">
        <div className="flex items-center space-x-2">
          <p className="text-sm font-medium">{t('rowsPerPage')}</p>
          <Select
            value={`${perPage}`}
            onValueChange={(value) => {
              const newPageSize = Number(value);
              table.setPageSize(newPageSize);
              onPageSizeChange(newPageSize);
            }}
          >
            <SelectTrigger className="h-8 w-[70px]">
              <SelectValue placeholder={`${perPage}`} />
            </SelectTrigger>
            <SelectContent side="top">
              {[10, 20, 25, 30, 40, 50].map((size) => (
                <SelectItem key={size} value={`${size}`}>
                  {size}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
        <div className="flex items-center justify-center text-sm font-medium">
          {t('page')} {currentPage} {t('of')} {totalPages}
        </div>
        <div className="flex items-center space-x-2">
          <Button
            variant="outline"
            size="icon"
            className="hidden size-8 lg:flex"
            onClick={() => {
              onPageChange(0);
            }}
            disabled={!canPrevious}
          >
            <span className="sr-only">{t('goToFirstPage')}</span>
            <FirstIcon />
          </Button>
          <Button
            variant="outline"
            size="icon"
            className="size-8"
            onClick={() => {
              onPageChange(pageIndex - 1);
            }}
            disabled={!canPrevious}
          >
            <span className="sr-only">{t('goToPreviousPage')}</span>
            <PrevIcon />
          </Button>
          <Button
            variant="outline"
            size="icon"
            className="size-8"
            onClick={() => {
              onPageChange(pageIndex + 1);
            }}
            disabled={!canNext}
          >
            <span className="sr-only">{t('goToNextPage')}</span>
            <NextIcon />
          </Button>
          <Button
            variant="outline"
            size="icon"
            className="hidden size-8 lg:flex"
            onClick={() => {
              onPageChange(totalPages - 1);
            }}
            disabled={!canNext}
          >
            <span className="sr-only">{t('goToLastPage')}</span>
            <LastIcon />
          </Button>
        </div>
      </div>
    </div>
  );
};
