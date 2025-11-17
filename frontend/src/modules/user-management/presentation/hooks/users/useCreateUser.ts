import { type User } from '@/modules/common/domain/entities/User';
import { CreateUserUseCase } from '@/modules/user-management/application/use-cases/users/create-user-use-case';
import { UserRepository } from '@/modules/user-management/infrastructure/repositories/user-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { userConfig } from '../../config/user-config';

type CreateUserMutationContext = {
  previousUsers: [string[], ApiResponse<PaginatedApiResponse<User[]>> | undefined][];
};

export const useCreateUser = (): UseMutationResult<
  ApiResponse<User>,
  Error,
  Omit<User, 'id' | 'createdAt' | 'updatedAt'>,
  CreateUserMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<
    ApiResponse<User>,
    Error,
    Omit<User, 'id' | 'createdAt' | 'updatedAt'>,
    CreateUserMutationContext
  >({
    mutationFn: (user) => {
      const userRepository = new UserRepository();
      const createUserUseCase = new CreateUserUseCase(userRepository);
      return createUserUseCase.execute(user);
    },
    mutationKey: [userConfig.queryKey, 'create'],
    onError: (_, __, context) => {
      if (context?.previousUsers) {
        for (const [queryKey, data] of context.previousUsers) {
          queryClient.setQueryData(queryKey, data);
        }
      }
    },
    onMutate: async (newUser) => {
      const queryKeyPrefix = [userConfig.queryKey];
      await queryClient.cancelQueries({ queryKey: queryKeyPrefix });
      const previousUsers = queryClient.getQueriesData<ApiResponse<PaginatedApiResponse<User[]>>>({
        queryKey: queryKeyPrefix,
      }) as [string[], ApiResponse<PaginatedApiResponse<User[]>> | undefined][];
      const targetQueryKey = [userConfig.queryKey, 1, 10, '', JSON.stringify({})];
      queryClient.setQueriesData<ApiResponse<PaginatedApiResponse<User[]>>>(
        { queryKey: targetQueryKey },
        (old) => {
          if (!old?.data?.data) return old;
          const optimisticUser: User = {
            ...newUser,
            createdAt: new Date().toISOString(),
            id: -Date.now(),
            updatedAt: new Date().toISOString(),
          };
          return {
            ...old,
            data: {
              ...old.data,
              data: [optimisticUser, ...old.data.data],
              meta: {
                ...old.data.meta,
                to: old.data.meta.to + 1,
                total: old.data.meta.total + 1,
              },
            },
          };
        }
      );
      return { previousUsers };
    },
    onSettled: () => {
      queryClient.invalidateQueries({
        exact: false,
        queryKey: [userConfig.queryKey],
      });
    },
  });
};
