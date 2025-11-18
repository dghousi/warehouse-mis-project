import { UpdateRoleUseCase } from '@/modules/user-management/application/use-cases/roles/update-role-use-case';
import { type Role } from '@/modules/user-management/domain/entities/Role';
import { RoleRepository } from '@/modules/user-management/infrastructure/repositories/role-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { roleConfig } from '../../config/role-config';

type UpdateRoleMutationContext = {
  previousRoles: [string[], ApiResponse<PaginatedApiResponse<Role[]>> | undefined][];
  previousRole?: ApiResponse<Role>;
};

type UpdateRoleVariables = {
  id: string | number;
  data: Partial<Omit<Role, 'id' | 'createdAt' | 'updatedAt'>>;
};

export const useUpdateRole = (): UseMutationResult<
  ApiResponse<Role>,
  Error,
  UpdateRoleVariables,
  UpdateRoleMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<
    ApiResponse<Role>,
    Error,
    { id: string | number; data: Partial<Omit<Role, 'id' | 'createdAt' | 'updatedAt'>> },
    UpdateRoleMutationContext
  >({
    mutationFn: ({ data, id }) => {
      const roleRepository = new RoleRepository();
      const updateRoleUseCase = new UpdateRoleUseCase(roleRepository);
      return updateRoleUseCase.execute(id, data);
    },
    mutationKey: [roleConfig.queryKey, 'update'],
    onError: (_, { id }, context) => {
      if (context?.previousRoles) {
        for (const [queryKey, data] of context.previousRoles) {
          queryClient.setQueryData(queryKey, data);
        }
      }
      if (context?.previousRole) {
        queryClient.setQueryData([roleConfig.singleQueryKey, id], context.previousRole);
      }
    },
    onMutate: async ({ data, id }) => {
      const listQueryKey = [roleConfig.queryKey];
      const singleQueryKey = [roleConfig.singleQueryKey, id];
      await queryClient.cancelQueries({ queryKey: listQueryKey });
      await queryClient.cancelQueries({ queryKey: singleQueryKey });

      const previousRoles = queryClient.getQueriesData<ApiResponse<PaginatedApiResponse<Role[]>>>({
        queryKey: listQueryKey,
      }) as [string[], ApiResponse<PaginatedApiResponse<Role[]>> | undefined][];

      const previousRole = queryClient.getQueryData<ApiResponse<Role>>(singleQueryKey);

      queryClient.setQueriesData<ApiResponse<PaginatedApiResponse<Role[]>>>(
        { queryKey: listQueryKey },
        (old) => {
          if (!old?.data?.data) return old;
          return {
            ...old,
            data: {
              ...old.data,
              data: old.data.data.map((item) =>
                item.id === id ? { ...item, ...data, updatedAt: new Date().toISOString() } : item
              ),
            },
          };
        }
      );

      queryClient.setQueryData<ApiResponse<Role>>(singleQueryKey, (old) => {
        if (!old?.data) return old;
        return {
          ...old,
          data: { ...old.data, ...data, updatedAt: new Date().toISOString() },
        };
      });

      return { previousRole, previousRoles };
    },
    onSettled: (_, __, { id }) => {
      queryClient.invalidateQueries({
        exact: false,
        queryKey: [roleConfig.queryKey],
      });
      queryClient.invalidateQueries({
        exact: true,
        queryKey: [roleConfig.singleQueryKey, id],
      });
    },
  });
};
