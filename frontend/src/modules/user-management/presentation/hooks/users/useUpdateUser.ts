import { type User } from '@/modules/common/domain/entities/User';
import { UpdateUserUseCase } from '@/modules/user-management/application/use-cases/users/update-user-use-case';
import { UserRepository } from '@/modules/user-management/infrastructure/repositories/user-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { userConfig } from '../../config/user-config';

type UpdateUserMutationContext = {
  previousUsers: [string[], ApiResponse<PaginatedApiResponse<User[]>> | undefined][];
  previousUser?: ApiResponse<User>;
};

type UpdateUserVariables = {
  id: string | number;
  data: Partial<Omit<User, 'id' | 'createdAt' | 'updatedAt'>>;
};

export const useUpdateUser = (): UseMutationResult<
  ApiResponse<User>,
  Error,
  UpdateUserVariables,
  UpdateUserMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<
    ApiResponse<User>,
    Error,
    { id: string | number; data: Partial<Omit<User, 'id' | 'createdAt' | 'updatedAt'>> },
    UpdateUserMutationContext
  >({
    mutationFn: ({ data, id }) => {
      const userRepository = new UserRepository();
      const updateUserUseCase = new UpdateUserUseCase(userRepository);
      return updateUserUseCase.execute(id, data);
    },
    mutationKey: [userConfig.queryKey, 'update'],
    onError: (_, { id }, context) => {
      if (context?.previousUsers) {
        for (const [queryKey, data] of context.previousUsers) {
          queryClient.setQueryData(queryKey, data);
        }
      }
      if (context?.previousUser) {
        queryClient.setQueryData([userConfig.singleQueryKey, id], context.previousUser);
      }
    },
    onMutate: async ({ data, id }) => {
      const listQueryKey = [userConfig.queryKey];
      const singleQueryKey = [userConfig.singleQueryKey, id];
      await queryClient.cancelQueries({ queryKey: listQueryKey });
      await queryClient.cancelQueries({ queryKey: singleQueryKey });

      const previousUsers = queryClient.getQueriesData<ApiResponse<PaginatedApiResponse<User[]>>>({
        queryKey: listQueryKey,
      }) as [string[], ApiResponse<PaginatedApiResponse<User[]>> | undefined][];

      const previousUser = queryClient.getQueryData<ApiResponse<User>>(singleQueryKey);

      queryClient.setQueriesData<ApiResponse<PaginatedApiResponse<User[]>>>(
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

      queryClient.setQueryData<ApiResponse<User>>(singleQueryKey, (old) => {
        if (!old?.data) return old;
        return {
          ...old,
          data: { ...old.data, ...data, updatedAt: new Date().toISOString() },
        };
      });

      return { previousUser, previousUsers };
    },
    onSettled: (_, __, { id }) => {
      queryClient.invalidateQueries({
        exact: false,
        queryKey: [userConfig.queryKey],
      });
      queryClient.invalidateQueries({
        exact: true,
        queryKey: [userConfig.singleQueryKey, id],
      });
    },
  });
};
