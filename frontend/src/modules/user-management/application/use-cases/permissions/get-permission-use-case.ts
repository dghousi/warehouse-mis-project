import { type Permission } from '@/modules/common/domain/entities/Permission';
import { type PermissionRepositoryInterface } from '@/modules/user-management/domain/interfaces/PermissionRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class GetPermissionUseCase {
  constructor(private readonly permissionRepository: PermissionRepositoryInterface) {}

  async execute(id: string | number): Promise<ApiResponse<Permission>> {
    return this.permissionRepository.getPermission(id);
  }
}
