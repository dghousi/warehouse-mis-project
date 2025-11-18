import { CreateRoleUseCase } from '@/modules/user-management/application/use-cases/roles/create-role-use-case';
import { type Role } from '@/modules/user-management/domain/entities/Role';
import { RoleRepository } from '@/modules/user-management/infrastructure/repositories/role-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { roleConfig } from '../../config/role-config';

type CreateRoleMutationContext = {
  previousRoles: [string[], ApiResponse<PaginatedApiResponse<Role[]>> | undefined][];
};

export const useCreateRole = (): UseMutationResult<
  ApiResponse<Role>,
  Error,
  Omit<Role, 'id' | 'createdAt' | 'updatedAt'>,
  CreateRoleMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<
    ApiResponse<Role>,
    Error,
    Omit<Role, 'id' | 'createdAt' | 'updatedAt'>,
    CreateRoleMutationContext
  >({
    mutationFn: (role) => {
      const roleRepository = new RoleRepository();
      const createRoleUseCase = new CreateRoleUseCase(roleRepository);
      return createRoleUseCase.execute(role);
    },
    mutationKey: [roleConfig.queryKey, 'create'],
    onError: (_, __, context) => {
      if (context?.previousRoles) {
        for (const [queryKey, data] of context.previousRoles) {
          queryClient.setQueryData(queryKey, data);
        }
      }
    },
    onMutate: async (newRole) => {
      const queryKeyPrefix = [roleConfig.queryKey];
      await queryClient.cancelQueries({ queryKey: queryKeyPrefix });
      const previousRoles = queryClient.getQueriesData<ApiResponse<PaginatedApiResponse<Role[]>>>({
        queryKey: queryKeyPrefix,
      }) as [string[], ApiResponse<PaginatedApiResponse<Role[]>> | undefined][];
      const targetQueryKey = [roleConfig.queryKey, 1, 10, '', JSON.stringify({})];
      queryClient.setQueriesData<ApiResponse<PaginatedApiResponse<Role[]>>>(
        { queryKey: targetQueryKey },
        (old) => {
          if (!old?.data?.data) return old;
          const optimisticRole: Role = {
            ...newRole,
            createdAt: new Date().toISOString(),
            id: -Date.now(),
            updatedAt: new Date().toISOString(),
          };
          return {
            ...old,
            data: {
              ...old.data,
              data: [optimisticRole, ...old.data.data],
              meta: {
                ...old.data.meta,
                to: old.data.meta.to + 1,
                total: old.data.meta.total + 1,
              },
            },
          };
        }
      );
      return { previousRoles };
    },
    onSettled: () => {
      queryClient.invalidateQueries({
        exact: false,
        queryKey: [roleConfig.queryKey],
      });
    },
  });
};
