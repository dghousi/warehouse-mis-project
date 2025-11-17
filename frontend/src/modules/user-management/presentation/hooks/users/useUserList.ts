import { type User } from '@/modules/common/domain/entities/User';
import { ListUsersUseCase } from '@/modules/user-management/application/use-cases/users/list-users-use-case';
import { UserRepository } from '@/modules/user-management/infrastructure/repositories/user-repository';
import {
  type ApiErrorDetail,
  type ApiResponse,
  type PaginatedApiResponse,
  type PaginationLinks,
  type PaginationMeta,
} from '@/types/ApiResponse';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';
import { userConfig } from '../../config/user-config';

export type UserListResponse = {
  entities: User[];
  pagination?: PaginationMeta;
  links?: PaginationLinks;
  isFetching: boolean;
  error: ApiErrorDetail | null;
  message: string;
  refetch: () => Promise<void>;
};

export const useUserList = (
  page: number = 1,
  perPage: number = 10,
  search: string = '',
  searchFields: string[] = userConfig.searchFields?.map((field) => field.value) || [],
  relations: string[] = userConfig.relations ?? [],
  filters: Record<string, string[]> = {}
): UserListResponse => {
  const queryClient = useQueryClient();
  const queryKey = [
    userConfig.queryKey,
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
  } = useQuery<ApiResponse<PaginatedApiResponse<User[]>>>({
    enabled: !queryClient.isMutating({
      predicate: (mutation) =>
        mutation.options.mutationKey?.[0] === userConfig.queryKey &&
        ['create', 'update', 'delete'].includes(mutation.options.mutationKey?.[1] as string),
    }),
    gcTime: 10 * 60 * 1000,
    placeholderData: (previous) => previous,
    queryFn: async () => {
      const userRepository = new UserRepository();
      const listUsersUseCase = new ListUsersUseCase(userRepository);
      const response = await listUsersUseCase.execute(
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
          code: error.name || 'USER_LIST_ERROR',
          details: null,
          message: error.message || 'Failed to fetch users',
        }
      : (data?.error ?? null),
    isFetching,
    links: data?.data?.links,
    message: data?.message ?? error?.message ?? 'Users fetched successfully',
    pagination: data?.data?.meta,
    refetch,
  };
};
