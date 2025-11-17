import { type Role } from '@/modules/user-management/domain/entities/Role';
import { type RoleRepositoryInterface } from '@/modules/user-management/domain/interfaces/RoleRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class UpdateRoleUseCase {
  constructor(private readonly roleRepository: RoleRepositoryInterface) {}

  async execute(
    id: string | number,
    role: Partial<Omit<Role, 'id' | 'createdAt' | 'updatedAt'>>
  ): Promise<ApiResponse<Role>> {
    return await this.roleRepository.updateRole(id, role);
  }
}
