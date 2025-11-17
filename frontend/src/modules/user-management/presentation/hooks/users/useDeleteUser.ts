import { type User } from '@/modules/common/domain/entities/User';
import { DeleteUserUseCase } from '@/modules/user-management/application/use-cases/users/delete-user-use-case';
import { UserRepository } from '@/modules/user-management/infrastructure/repositories/user-repository';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { useMutation, UseMutationResult, useQueryClient } from '@tanstack/react-query';
import { userConfig } from '../../config/user-config';

type DeleteUserMutationContext = {
  previousUsers: [string[], ApiResponse<PaginatedApiResponse<User[]>> | undefined][];
  previousUser?: ApiResponse<User>;
};

export const useDeleteUser = (): UseMutationResult<
  ApiResponse<null>,
  Error,
  string | number,
  DeleteUserMutationContext
> => {
  const queryClient = useQueryClient();

  return useMutation<ApiResponse<null>, Error, string | number, DeleteUserMutationContext>({
    mutationFn: (id) => {
      const userRepository = new UserRepository();
      const deleteUserUseCase = new DeleteUserUseCase(userRepository);
      return deleteUserUseCase.execute(id);
    },
    mutationKey: [userConfig.queryKey, 'delete'],
    onError: (_, id, context) => {
      if (context?.previousUsers) {
        for (const [queryKey, data] of context.previousUsers) {
          queryClient.setQueryData(queryKey, data);
        }
      }
      if (context?.previousUser) {
        queryClient.setQueryData([userConfig.singleQueryKey, id], context.previousUser);
      }
    },
    onMutate: async (id) => {
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

      return { previousUser, previousUsers };
    },
    onSettled: (_, __, id) => {
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
