'use client';

import { JSX, type Dispatch, type SetStateAction } from 'react';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utilities';
import { type EntityModuleConfig } from '@/types/EntityModuleConfig';
import { type Table } from '@tanstack/react-table';
import { Check, PlusIcon, X } from 'lucide-react';
import { EntityTableFacetedFilter } from './entity-table-faceted-filter';

type EntityTableToolbarProps<TData> = {
  table: Table<TData>;
  config: EntityModuleConfig;
  renderDrawerForm?: () => React.ReactNode;
  onTrigger?: () => void;
  search?: string;
  setSearch?: Dispatch<SetStateAction<string>>;
  filters?: Record<string, string[]>;
  setFilters?: Dispatch<SetStateAction<Record<string, string[]>>>;
  selectedSearchFields: string[];
  setSelectedSearchFields: Dispatch<SetStateAction<string[]>>;
  drawerTriggerLabel?: string;
};

export const EntityTableToolbar = <TData,>({
  config,
  drawerTriggerLabel,
  filters,
  onTrigger,
  renderDrawerForm,
  search,
  selectedSearchFields,
  setFilters,
  setSearch,
  setSelectedSearchFields,
  table,
}: EntityTableToolbarProps<TData>): JSX.Element => {
  const isFiltered = !!(search || (filters && Object.keys(filters).length > 0));

  const handleSearchChange = (event: React.ChangeEvent<HTMLInputElement>): void => {
    if (setSearch) setSearch(event.target.value);
  };

  const handleResetFilters = (): void => {
    if (setSearch) setSearch('');
    if (setFilters) setFilters({});
    setSelectedSearchFields(config.searchFields?.map((field) => field.value));
    table.resetColumnFilters();
  };

  const handleSearchFieldToggle = (field: string): void => {
    setSelectedSearchFields((previous) =>
      previous.includes(field) ? previous.filter((f) => f !== field) : [...previous, field]
    );
  };

  const t = useTranslations(config.singleQueryKey);
  const tGlobal = useTranslations('global');

  return (
    <div className="flex items-center justify-between">
      <div className="flex flex-1 items-center gap-2">
        <Input
          id="search"
          name="search"
          placeholder={tGlobal('search', { field: tGlobal('multipleFields') })}
          value={search ?? ''}
          onChange={handleSearchChange}
          className="h-8 w-[150px] lg:w-[250px]"
        />
        {config.searchFields && (
          <Popover>
            <PopoverTrigger asChild>
              <Button variant="outline" size="sm" className="h-8 border-dashed">
                <PlusIcon className="h-4 w-4" />
                {tGlobal('selectSearchFields')}
                <span>({selectedSearchFields.length})</span>
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-[200px] p-0">
              <div className="p-2">
                {config.searchFields.map((field) => (
                  <button
                    key={field.value}
                    className="flex items-center gap-2 py-1 cursor-pointer"
                    onClick={() => {
                      handleSearchFieldToggle(field.value);
                    }}
                  >
                    <div
                      className={cn(
                        'flex size-4 items-center justify-center rounded-[4px] border',
                        selectedSearchFields.includes(field.value)
                          ? 'bg-primary border-primary text-primary-foreground'
                          : 'border-input [&_svg]:invisible'
                      )}
                    >
                      <Check className="text-primary-foreground size-3.5" />
                    </div>
                    <span>{t(field.label)}</span>
                  </button>
                ))}
              </div>
            </PopoverContent>
          </Popover>
        )}
        {config.filters?.map((filter) => (
          <EntityTableFacetedFilter
            key={filter.key}
            column={table.getColumn(filter.key)}
            config={config}
            title={t(filter.title)}
            options={filter.options}
            onFilterChange={(key, values) => {
              if (setFilters) {
                setFilters((previous) => ({ ...previous, [key]: values }));
              }
            }}
          />
        ))}
        {isFiltered && (
          <Button
            variant="ghost"
            size="sm"
            onClick={handleResetFilters}
            className="text-muted-foreground"
          >
            {tGlobal('reset')}
            <X className="ms-2 h-4 w-4" />
          </Button>
        )}
      </div>
      <div className="flex items-center gap-2">
        <Button onClick={onTrigger}>
          <PlusIcon className="w-4 h-4" />
          {drawerTriggerLabel || t(config.createButton)}
        </Button>
        {renderDrawerForm?.()}
      </div>
    </div>
  );
};
