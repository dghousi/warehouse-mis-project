import { type Permission } from '@/modules/common/domain/entities/Permission';
import { type PermissionRepositoryInterface } from '@/modules/user-management/domain/interfaces/PermissionRepositoryInterface';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';

export class ListPermissionsUseCase {
  constructor(private readonly permissionRepository: PermissionRepositoryInterface) {}

  async execute(
    search: string = '',
    searchFields: string[] = [],
    relations: string[] = [],
    filters: Record<string, string[]> = {},
    page: number = 1,
    perPage: number = 10
  ): Promise<ApiResponse<PaginatedApiResponse<Permission[]>>> {
    const relationsArray = Array.isArray(relations) ? relations : [];
    return this.permissionRepository.paginate(
      search,
      searchFields,
      relationsArray,
      filters,
      page,
      perPage
    );
  }
}
