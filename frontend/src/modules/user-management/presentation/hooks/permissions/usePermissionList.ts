import { type Permission } from '@/modules/common/domain/entities/Permission';
import { ListPermissionsUseCase } from '@/modules/user-management/application/use-cases/permissions/list-permissions-use-case';
import { PermissionRepository } from '@/modules/user-management/infrastructure/repositories/permission-repository';
import {
  type ApiErrorDetail,
  type ApiResponse,
  type PaginatedApiResponse,
  type PaginationLinks,
  type PaginationMeta,
} from '@/types/ApiResponse';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';
import { permissionConfig } from '../../config/permission-config';

export type PermissionListResponse = {
  entities: Permission[];
  pagination?: PaginationMeta;
  links?: PaginationLinks;
  isFetching: boolean;
  error: ApiErrorDetail | null;
  message: string;
  refetch: () => Promise<void>;
};

export const usePermissionList = (
  page: number = 1,
  perPage: number = 10,
  search: string = '',
  searchFields: string[] = permissionConfig.searchFields?.map((field) => field.value) || [],
  relations: string[] = permissionConfig.relations ?? [],
  filters: Record<string, string[]> = {}
): PermissionListResponse => {
  const queryClient = useQueryClient();
  const queryKey = [
    permissionConfig.queryKey,
    page,
    perPage,
    search,
    searchFields.join(','),
    relations,
    JSON.stringify(filters),
  ] as const;

  const {
    data,
    error,
    isFetching,
    refetch: queryRefetch,
  } = useQuery<ApiResponse<PaginatedApiResponse<Permission[]>>>({
    enabled: !queryClient.isMutating({
      predicate: (mutation) =>
        mutation.options.mutationKey?.[0] === permissionConfig.queryKey &&
        ['create', 'update', 'delete'].includes(mutation.options.mutationKey?.[1] as string),
    }),
    gcTime: 10 * 60 * 1000,
    placeholderData: (previous) => previous,
    queryFn: async () => {
      const permissionRepository = new PermissionRepository();
      const listPermissionsUseCase = new ListPermissionsUseCase(permissionRepository);
      const response = await listPermissionsUseCase.execute(
        search,
        searchFields,
        relations,
        filters,
        page,
        perPage
      );
      if (response.error) {
        toast.error(response.error.message || 'Failed to fetch users', {
          position: 'top-center',
          style: {
            background: '#ef4444',
            border: '1px solid #e5e7eb',
            color: '#ffffff',
          },
        });
      }
      return response;
    },
    queryKey,
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    retry: 2,
    staleTime: 5 * 60 * 1000,
  });

  const refetch = async (): Promise<void> => {
    await queryRefetch();
  };

  return {
    entities: data?.data?.data ?? [],
    error: error
      ? {
          code: error.name || 'PERMISSION_LIST_ERROR',
          details: null,
          message: error.message || 'Failed to fetch permissions',
        }
      : (data?.error ?? null),
    isFetching,
    links: data?.data?.links,
    message: data?.message ?? error?.message ?? 'Permissions fetched successfully',
    pagination: data?.data?.meta,
    refetch,
  };
};
