import { type RoleRepositoryInterface } from '@/modules/user-management/domain/interfaces/RoleRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class DeleteRoleUseCase {
  constructor(private readonly roleRepository: RoleRepositoryInterface) {}

  async execute(id: string | number): Promise<ApiResponse<null>> {
    return await this.roleRepository.deleteRole(id);
  }
}
