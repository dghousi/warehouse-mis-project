import { type Permission } from '@/modules/common/domain/entities/Permission';
import { GetPermissionUseCase } from '@/modules/user-management/application/use-cases/permissions/get-permission-use-case';
import { PermissionRepository } from '@/modules/user-management/infrastructure/repositories/permission-repository';
import { type ApiResponse } from '@/types/ApiResponse';
import { useQuery, useQueryClient, UseQueryResult } from '@tanstack/react-query';
import { permissionConfig } from '../../config/permission-config';

export const useGetPermission = (
  id: string | number
): UseQueryResult<ApiResponse<Permission>, unknown> => {
  const queryClient = useQueryClient();

  return useQuery<ApiResponse<Permission>>({
    enabled: !queryClient.isMutating({
      predicate: (mutation) =>
        mutation.options.mutationKey?.[0] === permissionConfig.queryKey &&
        ['create', 'update', 'delete'].includes(mutation.options.mutationKey?.[1] as string),
    }),
    gcTime: 10 * 60 * 1000,
    queryFn: () => {
      const permissionRepository = new PermissionRepository();
      const getPermissionUseCase = new GetPermissionUseCase(permissionRepository);
      return getPermissionUseCase.execute(id);
    },
    queryKey: [permissionConfig.singleQueryKey, id],
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    retry: 2,
    staleTime: 5 * 60 * 1000,
  });
};
