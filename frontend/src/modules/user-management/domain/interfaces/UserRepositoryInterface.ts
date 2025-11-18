import { type User } from '@/modules/common/domain/entities/User';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';

export type UserRepositoryInterface = {
  createUser: (user: Omit<User, 'id' | 'createdAt' | 'updatedAt'>) => Promise<ApiResponse<User>>;
  getUser: (id: string | number) => Promise<ApiResponse<User>>;
  updateUser: (
    id: string | number,
    user: Partial<Omit<User, 'id' | 'createdAt' | 'updatedAt'>>
  ) => Promise<ApiResponse<User>>;
  deleteUser: (id: string | number) => Promise<ApiResponse<null>>;
  paginate: (
    search?: string,
    searchFields?: string[],
    relations?: string[],
    filters?: Record<string, string[]>,
    page?: number,
    perPage?: number
  ) => Promise<ApiResponse<PaginatedApiResponse<User[]>>>;
};
