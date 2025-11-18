import { type PermissionRepositoryInterface } from '@/modules/user-management/domain/interfaces/PermissionRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class DeletePermissionUseCase {
  constructor(private readonly permissionRepository: PermissionRepositoryInterface) {}

  async execute(id: string | number): Promise<ApiResponse<null>> {
    return await this.permissionRepository.deletePermission(id);
  }
}
