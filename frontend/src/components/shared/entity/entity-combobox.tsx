// src/components/shared/entity/entity-combobox.tsx
'use client';

import * as React from 'react';
import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utilities';
import { ApiResponse } from '@/types/ApiResponse';
import { EntityModuleConfig } from '@/types/EntityModuleConfig';
import { useQueryClient } from '@tanstack/react-query';
import { Check, ChevronsUpDown } from 'lucide-react';
import { useDebounce } from 'use-debounce';

type EntityComboboxProps<T> = {
  label: string;
  valueKey: keyof T | string;
  value?: string | number;
  initialItem?: T;
  onChange: (value: string | number, item?: T) => void;
  fetchHook: (
    page: number,
    perPage: number,
    search: string,
    searchFields: string[],
    relations: string[],
    filters: Record<string, string[]>
  ) => {
    entities: T[];
    isFetching: boolean;
    refetch: () => Promise<void>;
  };
  fetchByIdHook?: (
    id: string | number,
    options?: { enabled?: boolean }
  ) => {
    data?: ApiResponse<T>;
    isFetching: boolean;
  };
  fetchById?: (id: string | number, queryClient: unknown) => Promise<T | null>;
  perPage?: number;
  config?: EntityModuleConfig;
  labelFormatter?: (item: T) => string;
  disabled?: boolean;
  queryKey?: string;
};

export const EntityCombobox = <T,>({
  config,
  disabled = false,
  fetchById,
  fetchByIdHook,
  fetchHook,
  initialItem,
  label,
  labelFormatter,
  onChange,
  perPage = 10,
  value,
  valueKey,
}: Readonly<EntityComboboxProps<T>>): JSX.Element => {
  const t = useTranslations('component.form');
  const queryClient = useQueryClient();

  const [open, setOpen] = React.useState(false);
  const [search, setSearch] = React.useState('');
  const [debouncedSearch] = useDebounce(search, 400);
  const [selectedItem, setSelectedItem] = React.useState<T | undefined>(initialItem);
  const [extraItem, setExtraItem] = React.useState<T | null>(null);
  const [fetchedValues, setFetchedValues] = React.useState<Set<string | number>>(new Set());

  const labelKey = React.useMemo(
    () =>
      config && !labelFormatter && config.formFields
        ? config.formFields.find((f) => f.name === valueKey && !f.render)?.name
        : undefined,
    [config, labelFormatter, valueKey]
  );

  const searchFields = React.useMemo(
    () => config?.searchFields?.map((f) => f.value) ?? (labelKey ? [labelKey] : []),
    [config, labelKey]
  );

  const relationsArray = config?.relations ?? [];

  const { entities, isFetching } = fetchHook(
    1,
    perPage,
    debouncedSearch,
    searchFields,
    relationsArray,
    {}
  );

  const { data: fetchedByIdData, isFetching: isFetchingById } = fetchByIdHook
    ? fetchByIdHook(value || '', { enabled: !!value && !fetchedValues.has(String(value)) })
    : { data: undefined, isFetching: false };

  // Stabilize getLabel and getValue
  const getLabel = React.useCallback(
    (item: T) => labelFormatter?.(item) ?? (labelKey ? String((item as any)[labelKey] ?? '') : ''),
    [labelFormatter, labelKey]
  );

  const getValue = React.useCallback(
    (item: T) => String((item as any)[valueKey] ?? ''),
    [valueKey]
  );

  const allEntities = React.useMemo(
    () =>
      extraItem && !entities.some((i) => getValue(i) === getValue(extraItem))
        ? [extraItem, ...entities].filter((item): item is T => item !== null)
        : entities,
    [entities, extraItem, getValue]
  );

  const areItemsEqual = React.useCallback(
    (a?: T, b?: T): boolean => (a && b ? getValue(a) === getValue(b) : a === b),
    [getValue]
  );

  const valStr = String(value ?? '');
  const fetchedItem = fetchedByIdData?.data;

  React.useEffect(() => {
    if (!value) {
      if (selectedItem !== undefined) setSelectedItem(undefined);
      if (extraItem !== null) setExtraItem(null);
      if (fetchedValues.size > 0) setFetchedValues(new Set());
    }
  }, [value, selectedItem, extraItem, fetchedValues]);

  React.useEffect(() => {
    if (!value) return;

    const found = entities.find((i) => getValue(i) === valStr);
    if (found && !areItemsEqual(found, selectedItem)) {
      setSelectedItem(found);
      if (extraItem !== null) setExtraItem(null);
    }
  }, [value, entities, selectedItem, extraItem, getValue, areItemsEqual, valStr]);

  React.useEffect(() => {
    if (!value || fetchedValues.has(valStr)) return;

    if (fetchedItem && !areItemsEqual(fetchedItem, selectedItem)) {
      setSelectedItem(fetchedItem);
      setExtraItem(fetchedItem);
      setFetchedValues((prev) => new Set([...prev, valStr]));
      return;
    }

    if (fetchById && !fetchedItem) {
      fetchById(value, queryClient).then((item) => {
        if (item && !areItemsEqual(item, selectedItem)) {
          setSelectedItem(item);
          setExtraItem(item);
          setFetchedValues((prev) => new Set([...prev, valStr]));
        }
      });
    }
  }, [
    value,
    fetchedItem,
    fetchById,
    queryClient,
    fetchedValues,
    selectedItem,
    valStr,
    areItemsEqual,
  ]);

  React.useEffect(() => {
    if (initialItem && selectedItem !== initialItem) {
      setSelectedItem(initialItem);
      setSearch(getLabel(initialItem));
    }
  }, [initialItem, getLabel, selectedItem]);

  const handleSelect = (item: T): void => {
    setSelectedItem(item);
    onChange(getValue(item), item);
    setOpen(false);
  };

  return (
    <div className="space-y-1.5">
      {label && <label className="text-sm font-medium">{label}</label>}
      <Popover open={open} onOpenChange={setOpen}>
        <PopoverTrigger asChild>
          <Button
            variant="outline"
            aria-expanded={open}
            disabled={disabled || isFetching || isFetchingById}
            className="w-full justify-between"
          >
            {selectedItem ? getLabel(selectedItem) : label || t('search')}
            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 opacity-50" />
          </Button>
        </PopoverTrigger>
        <PopoverContent align="start" className="w-[var(--radix-popover-trigger-width)] p-0">
          <Command shouldFilter={false}>
            <CommandInput
              placeholder={label || t('search')}
              value={search}
              onValueChange={setSearch}
              className="h-9"
              disabled={disabled}
            />
            <CommandList>
              {isFetching && !allEntities.length && (
                <div className="py-6 text-center text-sm text-muted-foreground">{t('loading')}</div>
              )}
              {!isFetching && !allEntities.length && (
                <CommandEmpty>{t('noResultsFound')}</CommandEmpty>
              )}
              <CommandGroup>
                {allEntities.map((item) => {
                  const itemValue = getValue(item);
                  return (
                    <CommandItem
                      key={itemValue}
                      value={itemValue}
                      onSelect={() => {
                        handleSelect(item);
                      }}
                    >
                      {getLabel(item)}
                      <Check
                        className={cn(
                          'ml-auto h-4 w-4',
                          selectedItem && getValue(selectedItem) === itemValue
                            ? 'opacity-100'
                            : 'opacity-0'
                        )}
                      />
                    </CommandItem>
                  );
                })}
              </CommandGroup>
            </CommandList>
          </Command>
        </PopoverContent>
      </Popover>
    </div>
  );
};
