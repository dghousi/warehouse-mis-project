import { type Permission } from '@/modules/common/domain/entities/Permission';
import { DeletePermissionUseCase } from '@/modules/user-management/application/use-cases/permissions/delete-permission-use-case';
import { PermissionRepository } from '@/modules/user-management/infrastructure/repositories/permission-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { permissionConfig } from '../../config/permission-config';

type DeletePermissionMutationContext = {
  previousPermissions: [string[], ApiResponse<PaginatedApiResponse<Permission[]>> | undefined][];
  previousPermission?: ApiResponse<Permission>;
};

export const useDeletePermission = (): UseMutationResult<
  ApiResponse<null>,
  Error,
  string | number,
  DeletePermissionMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<ApiResponse<null>, Error, string | number, DeletePermissionMutationContext>({
    mutationFn: (id) => {
      const permissionRepository = new PermissionRepository();
      const deletePermissionUseCase = new DeletePermissionUseCase(permissionRepository);
      return deletePermissionUseCase.execute(id);
    },
    mutationKey: [permissionConfig.queryKey, 'delete'],
    onError: (_, id, context) => {
      if (context?.previousPermissions) {
        for (const [queryKey, data] of context.previousPermissions) {
          queryClient.setQueryData(queryKey, data);
        }
      }
      if (context?.previousPermission) {
        queryClient.setQueryData([permissionConfig.singleQueryKey, id], context.previousPermission);
      }
    },
    onMutate: async (id) => {
      const listQueryKey = [permissionConfig.queryKey];
      const singleQueryKey = [permissionConfig.singleQueryKey, id];
      await queryClient.cancelQueries({ queryKey: listQueryKey });
      await queryClient.cancelQueries({ queryKey: singleQueryKey });

      const previousPermissions = queryClient.getQueriesData<
        ApiResponse<PaginatedApiResponse<Permission[]>>
      >({
        queryKey: listQueryKey,
      }) as [string[], ApiResponse<PaginatedApiResponse<Permission[]>> | undefined][];

      const previousPermission = queryClient.getQueryData<ApiResponse<Permission>>(singleQueryKey);

      queryClient.setQueriesData<ApiResponse<PaginatedApiResponse<Permission[]>>>(
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

      return { previousPermission, previousPermissions };
    },
    onSettled: (_, __, id) => {
      queryClient.invalidateQueries({
        exact: false,
        queryKey: [permissionConfig.queryKey],
      });
      queryClient.invalidateQueries({
        exact: true,
        queryKey: [permissionConfig.singleQueryKey, id],
      });
    },
  });
};
