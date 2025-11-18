import { type Permission } from '@/modules/common/domain/entities/Permission';
import { AxiosClient } from '@/modules/common/infrastructure/api/axios-client';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { type PermissionRepositoryInterface } from '../../domain/interfaces/PermissionRepositoryInterface';

export class PermissionRepository implements PermissionRepositoryInterface {
  private readonly client: AxiosClient;

  constructor() {
    this.client = new AxiosClient();
  }

  private async fetchCsrf(): Promise<void> {
    await this.client.get('/sanctum/csrf-cookie');
  }

  async createPermission(
    permission: Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>
  ): Promise<ApiResponse<Permission>> {
    try {
      await this.fetchCsrf();
      return await this.client.post<Permission>('/api/v1/user-management/permissions', permission);
    } catch (error: unknown) {
      return this.handleError<Permission>(
        error,
        'PERMISSION_CREATE_ERROR',
        'Failed to create permission'
      );
    }
  }

  async getPermission(id: string | number): Promise<ApiResponse<Permission>> {
    try {
      return await this.client.get<Permission>(`/api/v1/user-management/permissions/${id}`);
    } catch (error: unknown) {
      return this.handleError<Permission>(
        error,
        'PERMISSION_FETCH_ERROR',
        'Failed to fetch permission'
      );
    }
  }

  async updatePermission(
    id: string | number,
    permission: Partial<Omit<Permission, 'id' | 'createdAt' | 'updatedAt'>>
  ): Promise<ApiResponse<Permission>> {
    try {
      await this.fetchCsrf();
      const payload = { ...permission };
      return await this.client.put<Permission>(
        `/api/v1/user-management/permissions/${id}`,
        payload
      );
    } catch (error: unknown) {
      return this.handleError<Permission>(
        error,
        'PERMISSION_UPDATE_ERROR',
        'Failed to update permission'
      );
    }
  }

  async deletePermission(id: string | number): Promise<ApiResponse<null>> {
    try {
      await this.fetchCsrf();
      return await this.client.delete<null>(`/api/v1/user-management/permissions/${id}`);
    } catch (error: unknown) {
      return this.handleError<null>(
        error,
        'PERMISSION_DELETE_ERROR',
        'Failed to delete permission'
      );
    }
  }

  async paginate(
    search = '',
    searchFields: string[] = [],
    relations: string[] = [],
    filters: Record<string, string[]> = {},
    page = 1,
    perPage = 10
  ): Promise<ApiResponse<PaginatedApiResponse<Permission[]>>> {
    try {
      const parameters = new URLSearchParams({
        page: String(page),
        perPage: String(perPage),
        ...(search && { search }),
      });

      for (const field of searchFields) {
        parameters.append('searchFields[]', field);
      }

      for (const relation of relations) {
        parameters.append('include[]', relation);
      }

      for (const [key, values] of Object.entries(filters)) {
        for (const value of values) {
          parameters.append(`filter[${key}][]`, value);
        }
      }

      return await this.client.get<PaginatedApiResponse<Permission[]>>(
        `/api/v1/user-management/permissions`,
        {
          params: parameters,
        }
      );
    } catch (error: unknown) {
      return this.handleError<PaginatedApiResponse<Permission[]>>(
        error,
        'PERMISSION_LIST_ERROR',
        'Failed to fetch permissions'
      );
    }
  }

  private handleError<T>(error: unknown, code: string, defaultMessage: string): ApiResponse<T> {
    const responseError =
      error instanceof Error && 'response' in error ? (error as any).response : null;
    const errorData = responseError?.data?.error ?? responseError?.data;

    return {
      data: null,
      error: {
        code: errorData?.code ?? code,
        details: errorData?.details ?? null,
        message: errorData?.message ?? (error instanceof Error ? error.message : defaultMessage),
      },
      message: errorData?.message ?? (error instanceof Error ? error.message : defaultMessage),
      success: false,
    };
  }
}
