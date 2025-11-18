import { type User } from '@/modules/common/domain/entities/User';
import { type UserRepositoryInterface } from '@/modules/user-management/domain/interfaces/UserRepositoryInterface';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';

export class ListUsersUseCase {
  constructor(private readonly userRepository: UserRepositoryInterface) {}

  async execute(
    search: string = '',
    searchFields: string[] = [],
    relations: string[] = [],
    filters: Record<string, string[]> = {},
    page: number = 1,
    perPage: number = 10
  ): Promise<ApiResponse<PaginatedApiResponse<User[]>>> {
    const relationsArray = Array.isArray(relations) ? relations : [];
    return this.userRepository.paginate(
      search,
      searchFields,
      relationsArray,
      filters,
      page,
      perPage
    );
  }
}
