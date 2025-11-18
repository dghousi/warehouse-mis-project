import { type Permission } from '@/modules/common/domain/entities/Permission';
import { CreatePermissionUseCase } from '@/modules/user-management/application/use-cases/permissions/create-permission-use-case';
import { PermissionRepository } from '@/modules/user-management/infrastructure/repositories/permission-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { permissionConfig } from '../../config/permission-config';

type CreatePermissionMutationContext = {
  previousPermissions: [string[], ApiResponse<PaginatedApiResponse<Permission[]>> | undefined][];
};

export const useCreatePermission = (): UseMutationResult<
  ApiResponse<Permission>,
  Error,
  Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>,
  CreatePermissionMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<
    ApiResponse<Permission>,
    Error,
    Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>,
    CreatePermissionMutationContext
  >({
    mutationFn: (permission) => {
      const permissionRepository = new PermissionRepository();
      const createPermissionUseCase = new CreatePermissionUseCase(permissionRepository);
      return createPermissionUseCase.execute(permission);
    },
    mutationKey: [permissionConfig.queryKey, 'create'],
    onError: (_, __, context) => {
      if (context?.previousPermissions) {
        for (const [queryKey, data] of context.previousPermissions) {
          queryClient.setQueryData(queryKey, data);
        }
      }
    },
    onMutate: async (newPermission) => {
      const queryKeyPrefix = [permissionConfig.queryKey];
      await queryClient.cancelQueries({ queryKey: queryKeyPrefix });
      const previousPermissions = queryClient.getQueriesData<
        ApiResponse<PaginatedApiResponse<Permission[]>>
      >({
        queryKey: queryKeyPrefix,
      }) as [string[], ApiResponse<PaginatedApiResponse<Permission[]>> | undefined][];
      const targetQueryKey = [permissionConfig.queryKey, 1, 10, '', JSON.stringify({})];
      queryClient.setQueriesData<ApiResponse<PaginatedApiResponse<Permission[]>>>(
        { queryKey: targetQueryKey },
        (old) => {
          if (!old?.data?.data) return old;
          const optimisticPermission: Permission = {
            ...newPermission,
            createdAt: new Date().toISOString(),
            id: -Date.now(),
            updatedAt: new Date().toISOString(),
          };
          return {
            ...old,
            data: {
              ...old.data,
              data: [optimisticPermission, ...old.data.data],
              meta: {
                ...old.data.meta,
                to: old.data.meta.to + 1,
                total: old.data.meta.total + 1,
              },
            },
          };
        }
      );
      return { previousPermissions };
    },
    onSettled: () => {
      queryClient.invalidateQueries({
        exact: false,
        queryKey: [permissionConfig.queryKey],
      });
    },
  });
};
