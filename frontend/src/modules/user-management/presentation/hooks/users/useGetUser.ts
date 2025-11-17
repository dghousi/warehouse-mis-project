import { type User } from '@/modules/common/domain/entities/User';
import { GetUserUseCase } from '@/modules/user-management/application/use-cases/users/get-user-use-case';
import { UserRepository } from '@/modules/user-management/infrastructure/repositories/user-repository';
import { type ApiResponse } from '@/types/ApiResponse';
import { useQuery, useQueryClient, UseQueryResult } from '@tanstack/react-query';
import { userConfig } from '../../config/user-config';

export const useGetUser = (
  id: string | number,
  options: { enabled?: boolean } = {}
): UseQueryResult<ApiResponse<User>, unknown> => {
  const queryClient = useQueryClient();

  return useQuery<ApiResponse<User>>({
    enabled:
      !!id &&
      (options.enabled ?? true) &&
      !queryClient.isMutating({
        predicate: (mutation) =>
          mutation.options.mutationKey?.[0] === userConfig.queryKey &&
          ['create', 'update', 'delete'].includes(mutation.options.mutationKey?.[1] as string),
      }),
    gcTime: 10 * 60 * 1000,
    queryFn: () => {
      const userRepository = new UserRepository();
      const getUserUseCase = new GetUserUseCase(userRepository);
      return getUserUseCase.execute(id);
    },
    queryKey: [userConfig.singleQueryKey, id],
    refetchOnMount: false,
    refetchOnWindowFocus: false,
    retry: 2,
    staleTime: 5 * 60 * 1000,
  });
};
