import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { type Role } from '../entities/Role';

export type RoleRepositoryInterface = {
  createRole: (role: Omit<Role, 'id' | 'createdAt' | 'updatedAt'>) => Promise<ApiResponse<Role>>;
  getRole: (id: string | number) => Promise<ApiResponse<Role>>;
  updateRole: (
    id: string | number,
    role: Partial<Omit<Role, 'id' | 'createdAt' | 'updatedAt'>>
  ) => Promise<ApiResponse<Role>>;
  deleteRole: (id: string | number) => Promise<ApiResponse<null>>;
  paginate: (
    search?: string,
    searchFields?: string[],
    relations?: string[],
    filters?: Record<string, string[]>,
    page?: number,
    perPage?: number
  ) => Promise<ApiResponse<PaginatedApiResponse<Role[]>>>;
};
