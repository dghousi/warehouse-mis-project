'use client';

import * as React from 'react';
import { JSX } from 'react';
import { useTranslations } from 'next-intl';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utilities';
import { EntityModuleConfig } from '@/types/EntityModuleConfig';
import { Column } from '@tanstack/react-table';
import { Check, PlusCircle } from 'lucide-react';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from './entity-command';

type EntityTableFacetedFilterProps<TData, TValue> = {
  column?: Column<TData, TValue>;
  config: EntityModuleConfig;
  title?: string;
  options: {
    label: string;
    value: any;
    icon?: React.ComponentType<{ className?: string }>;
  }[];
  onFilterChange?: (key: string, values: string[]) => void;
};

export const EntityTableFacetedFilter = <TData, TValue>({
  column,
  config,
  onFilterChange,
  options,
  title,
}: Readonly<EntityTableFacetedFilterProps<TData, TValue>>): JSX.Element => {
  const t = useTranslations(config.singleQueryKey);
  const tComponent = useTranslations('component.table');

  const facets = column?.getFacetedUniqueValues();
  const selectedValues = new Set((column?.getFilterValue() as string[]) || []);

  const handleFilterChange = (values: string[]): void => {
    column?.setFilterValue(values.length ? values : undefined);
    if (onFilterChange && column?.id) {
      onFilterChange(column.id, values);
    }
  };

  return (
    <Popover>
      <PopoverTrigger asChild>
        <Button variant="outline" size="sm" className="h-8 border-dashed">
          <PlusCircle />
          {title}
          {selectedValues?.size > 0 && (
            <>
              <Separator orientation="vertical" className="mx-2 h-4" />
              <Badge variant="secondary" className="rounded-sm px-1 font-normal lg:hidden">
                {selectedValues.size}
              </Badge>
              <div className="hidden gap-1 lg:flex">
                {selectedValues.size > 2 ? (
                  <Badge variant="secondary" className="rounded-sm px-1 font-normal">
                    {tComponent('selected', { count: selectedValues.size })}
                  </Badge>
                ) : (
                  options
                    .filter((option) => selectedValues.has(option.value))
                    .map((option) => (
                      <Badge
                        variant="secondary"
                        key={option.value}
                        className="rounded-sm px-1 font-normal"
                      >
                        {t(option.label)}
                      </Badge>
                    ))
                )}
              </div>
            </>
          )}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-[200px] p-0" align="start">
        <Command>
          <CommandInput placeholder={title} />
          <CommandList>
            <CommandEmpty>{tComponent('noResultsFound')}</CommandEmpty>
            <CommandGroup>
              {options.map((option) => {
                const isSelected = selectedValues.has(option.value);
                return (
                  <CommandItem
                    key={option.value}
                    onSelect={() => {
                      if (isSelected) {
                        selectedValues.delete(option.value);
                      } else {
                        selectedValues.add(option.value);
                      }
                      const filterValues = Array.from(selectedValues);
                      handleFilterChange(filterValues);
                    }}
                  >
                    <div
                      className={cn(
                        'flex size-4 items-center justify-center rounded-[4px] border',
                        isSelected
                          ? 'bg-primary border-primary text-primary-foreground'
                          : 'border-input [&_svg]:invisible'
                      )}
                    >
                      <Check className="text-primary-foreground size-3.5" />
                    </div>
                    {option.icon && <option.icon className="text-muted-foreground size-4" />}
                    <span>{t(option.label)}</span>
                    {facets?.get(option.value) && (
                      <span className="text-muted-foreground ms-auto flex size-4 items-center justify-center font-mono text-xs">
                        {facets.get(option.value)}
                      </span>
                    )}
                  </CommandItem>
                );
              })}
            </CommandGroup>
            {selectedValues.size > 0 && (
              <>
                <CommandSeparator />
                <CommandGroup>
                  <CommandItem
                    onSelect={() => {
                      handleFilterChange([]);
                    }}
                    className="justify-center text-center"
                  >
                    {tComponent('clearFilters')}
                  </CommandItem>
                </CommandGroup>
              </>
            )}
          </CommandList>
        </Command>
      </PopoverContent>
    </Popover>
  );
};
