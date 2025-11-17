import { type Role } from '@/modules/user-management/domain/entities/Role';
import { type RoleRepositoryInterface } from '@/modules/user-management/domain/interfaces/RoleRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class GetRoleUseCase {
  constructor(private readonly roleRepository: RoleRepositoryInterface) {}

  async execute(id: string | number): Promise<ApiResponse<Role>> {
    return this.roleRepository.getRole(id);
  }
}
