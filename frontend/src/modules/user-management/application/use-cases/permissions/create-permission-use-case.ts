import { type Permission } from '@/modules/common/domain/entities/Permission';
import { type PermissionRepositoryInterface } from '@/modules/user-management/domain/interfaces/PermissionRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class CreatePermissionUseCase {
  constructor(private readonly permissionRepository: PermissionRepositoryInterface) {}

  async execute(
    permission: Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>
  ): Promise<ApiResponse<Permission>> {
    return await this.permissionRepository.createPermission(permission);
  }
}
