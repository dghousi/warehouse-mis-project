import { ListRolesUseCase } from '@/modules/user-management/application/use-cases/roles/list-roles-use-case';
import { type Role } from '@/modules/user-management/domain/entities/Role';
import { RoleRepository } from '@/modules/user-management/infrastructure/repositories/role-repository';
import {
  type ApiErrorDetail,
  type ApiResponse,
  type PaginatedApiResponse,
  type PaginationLinks,
  type PaginationMeta,
} from '@/types/ApiResponse';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';
import { roleConfig } from '../../config/role-config';

export type RoleListResponse = {
  entities: Role[];
  pagination?: PaginationMeta;
  links?: PaginationLinks;
  isFetching: boolean;
  error: ApiErrorDetail | null;
  message: string;
  refetch: () => Promise<void>;
};

export const useRoleList = (
  page: number = 1,
  perPage: number = 10,
  search: string = '',
  searchFields: string[] = roleConfig.searchFields?.map((field) => field.value) || [],
  relations: string[] = roleConfig.relations ?? [],
  filters: Record<string, string[]> = {}
): RoleListResponse => {
  const queryClient = useQueryClient();
  const queryKey = [
    roleConfig.queryKey,
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
  } = useQuery<ApiResponse<PaginatedApiResponse<Role[]>>>({
    enabled: !queryClient.isMutating({
      predicate: (mutation) =>
        mutation.options.mutationKey?.[0] === roleConfig.queryKey &&
        ['create', 'update', 'delete'].includes(mutation.options.mutationKey?.[1] as string),
    }),
    gcTime: 10 * 60 * 1000,
    placeholderData: (previous) => previous,
    queryFn: async () => {
      const roleRepository = new RoleRepository();
      const listRolesUseCase = new ListRolesUseCase(roleRepository);
      const response = await listRolesUseCase.execute(
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
          code: error.name || 'ROLE_LIST_ERROR',
          details: null,
          message: error.message || 'Failed to fetch roles',
        }
      : (data?.error ?? null),
    isFetching,
    links: data?.data?.links,
    message: data?.message ?? error?.message ?? 'Roles fetched successfully',
    pagination: data?.data?.meta,
    refetch,
  };
};
