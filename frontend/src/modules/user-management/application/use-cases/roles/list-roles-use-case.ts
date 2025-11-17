import { type Role } from '@/modules/user-management/domain/entities/Role';
import { type RoleRepositoryInterface } from '@/modules/user-management/domain/interfaces/RoleRepositoryInterface';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';

export class ListRolesUseCase {
  constructor(private readonly roleRepository: RoleRepositoryInterface) {}

  async execute(
    search: string = '',
    searchFields: string[] = [],
    relations: string[] = [],
    filters: Record<string, string[]> = {},
    page: number = 1,
    perPage: number = 10
  ): Promise<ApiResponse<PaginatedApiResponse<Role[]>>> {
    const relationsArray = Array.isArray(relations) ? relations : [];
    return this.roleRepository.paginate(
      search,
      searchFields,
      relationsArray,
      filters,
      page,
      perPage
    );
  }
}
