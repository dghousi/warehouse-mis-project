import { GetRoleUseCase } from '@/modules/user-management/application/use-cases/roles/get-role-use-case';
import { type Role } from '@/modules/user-management/domain/entities/Role';
import { RoleRepository } from '@/modules/user-management/infrastructure/repositories/role-repository';
import { type ApiResponse } from '@/types/ApiResponse';
import { useQuery, useQueryClient, UseQueryResult } from '@tanstack/react-query';
import { roleConfig } from '../../config/role-config';

export const useGetRole = (id: string | number): UseQueryResult<ApiResponse<Role>, unknown> => {
  const queryClient = useQueryClient();

  return useQuery<ApiResponse<Role>>({
    enabled: !queryClient.isMutating({
      predicate: (mutation) =>
        mutation.options.mutationKey?.[0] === roleConfig.queryKey &&
        ['create', 'update', 'delete'].includes(mutation.options.mutationKey?.[1] as string),
    }),
    gcTime: 10 * 60 * 1000,
    queryFn: () => {
      const roleRepository = new RoleRepository();
      const getRoleUseCase = new GetRoleUseCase(roleRepository);
      return getRoleUseCase.execute(id);
    },
    queryKey: [roleConfig.singleQueryKey, id],
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    retry: 2,
    staleTime: 5 * 60 * 1000,
  });
};
