import { type Role } from '@/modules/user-management/domain/entities/Role';
import { type RoleRepositoryInterface } from '@/modules/user-management/domain/interfaces/RoleRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class CreateRoleUseCase {
  constructor(private readonly roleRepository: RoleRepositoryInterface) {}

  async execute(role: Omit<Role, 'id' | 'createdAt' | 'updatedAt'>): Promise<ApiResponse<Role>> {
    return await this.roleRepository.createRole(role);
  }
}
