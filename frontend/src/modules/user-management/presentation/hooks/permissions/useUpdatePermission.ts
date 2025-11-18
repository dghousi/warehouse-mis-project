import { type Permission } from '@/modules/common/domain/entities/Permission';
import { UpdatePermissionUseCase } from '@/modules/user-management/application/use-cases/permissions/update-permission-use-case';
import { PermissionRepository } from '@/modules/user-management/infrastructure/repositories/permission-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { permissionConfig } from '../../config/permission-config';

type UpdatePermissionMutationContext = {
  previousPermissions: [string[], ApiResponse<PaginatedApiResponse<Permission[]>> | undefined][];
  previousPermission?: ApiResponse<Permission>;
};

type UpdatePermissionVariables = {
  id: string | number;
  data: Partial<Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>>;
};

export const useUpdatePermission = (): UseMutationResult<
  ApiResponse<Permission>,
  Error,
  UpdatePermissionVariables,
  UpdatePermissionMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<
    ApiResponse<Permission>,
    Error,
    { id: string | number; data: Partial<Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>> },
    UpdatePermissionMutationContext
  >({
    mutationFn: ({ data, id }) => {
      const permissionRepository = new PermissionRepository();
      const updatePermissionUseCase = new UpdatePermissionUseCase(permissionRepository);
      return updatePermissionUseCase.execute(id, data);
    },
    mutationKey: [permissionConfig.queryKey, 'update'],
    onError: (_, { id }, context) => {
      if (context?.previousPermissions) {
        for (const [queryKey, data] of context.previousPermissions) {
          queryClient.setQueryData(queryKey, data);
        }
      }
      if (context?.previousPermission) {
        queryClient.setQueryData([permissionConfig.singleQueryKey, id], context.previousPermission);
      }
    },
    onMutate: async ({ data, id }) => {
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
              data: old.data.data.map((item) =>
                item.id === id ? { ...item, ...data, updatedAt: new Date().toISOString() } : item
              ),
            },
          };
        }
      );

      queryClient.setQueryData<ApiResponse<Permission>>(singleQueryKey, (old) => {
        if (!old?.data) return old;
        return {
          ...old,
          data: { ...old.data, ...data, updatedAt: new Date().toISOString() },
        };
      });

      return { previousPermission, previousPermissions };
    },
    onSettled: (_, __, { id }) => {
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
