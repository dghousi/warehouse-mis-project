import { type Permission } from '@/modules/common/domain/entities/Permission';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';

export type PermissionRepositoryInterface = {
  createPermission: (
    permission: Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>
  ) => Promise<ApiResponse<Permission>>;
  getPermission: (id: string | number) => Promise<ApiResponse<Permission>>;
  updatePermission: (
    id: string | number,
    permission: Partial<Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>>
  ) => Promise<ApiResponse<Permission>>;
  deletePermission: (id: string | number) => Promise<ApiResponse<null>>;
  paginate: (
    search?: string,
    searchFields?: string[],
    relations?: string[],
    filters?: Record<string, string[]>,
    page?: number,
    perPage?: number
  ) => Promise<ApiResponse<PaginatedApiResponse<Permission[]>>>;
};
