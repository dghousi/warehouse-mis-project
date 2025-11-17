import { DeleteRoleUseCase } from '@/modules/user-management/application/use-cases/roles/delete-role-use-case';
import { RoleRepository } from '@/modules/user-management/infrastructure/repositories/role-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { type Role } from '../../../domain/entities/Role';
import { roleConfig } from '../../config/role-config';

type DeleteRoleMutationContext = {
  previousRoles: [string[], ApiResponse<PaginatedApiResponse<Role[]>> | undefined][];
  previousRole?: ApiResponse<Role>;
};

export const useDeleteRole = (): UseMutationResult<
  ApiResponse<null>,
  Error,
  string | number,
  DeleteRoleMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<ApiResponse<null>, Error, string | number, DeleteRoleMutationContext>({
    mutationFn: (id) => {
      const roleRepository = new RoleRepository();
      const deleteRoleUseCase = new DeleteRoleUseCase(roleRepository);
      return deleteRoleUseCase.execute(id);
    },
    mutationKey: [roleConfig.queryKey, 'delete'],
    onError: (_, id, context) => {
      if (context?.previousRoles) {
        for (const [queryKey, data] of context.previousRoles) {
          queryClient.setQueryData(queryKey, data);
        }
      }
      if (context?.previousRole) {
        queryClient.setQueryData([roleConfig.singleQueryKey, id], context.previousRole);
      }
    },
    onMutate: async (id) => {
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
              data: old.data.data.filter((item) => item.id !== id),
              meta: {
                ...old.data.meta,
                to: old.data.meta.to - 1,
                total: old.data.meta.total - 1,
              },
            },
          };
        }
      );

      queryClient.removeQueries({ queryKey: singleQueryKey });

      return { previousRole, previousRoles };
    },
    onSettled: (_, __, id) => {
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
