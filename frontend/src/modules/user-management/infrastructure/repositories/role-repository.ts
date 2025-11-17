import { AxiosClient } from '@/modules/common/infrastructure/api/axios-client';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { type Role } from '../../domain/entities/Role';
import { type RoleRepositoryInterface } from '../../domain/interfaces/RoleRepositoryInterface';

export class RoleRepository implements RoleRepositoryInterface {
  private readonly client: AxiosClient;

  constructor() {
    this.client = new AxiosClient();
  }

  private async fetchCsrf(): Promise<void> {
    await this.client.get('/sanctum/csrf-cookie');
  }

  async createRole(role: Omit<Role, 'id' | 'createdAt' | 'updatedAt'>): Promise<ApiResponse<Role>> {
    try {
      await this.fetchCsrf();
      return await this.client.post<Role>('/api/v1/user-management/roles', role);
    } catch (error: unknown) {
      return this.handleError<Role>(error, 'ROLE_CREATE_ERROR', 'Failed to create role');
    }
  }

  async getRole(id: string | number): Promise<ApiResponse<Role>> {
    try {
      return await this.client.get<Role>(`/api/v1/user-management/roles/${id}`);
    } catch (error: unknown) {
      return this.handleError<Role>(error, 'ROLE_FETCH_ERROR', 'Failed to fetch role');
    }
  }

  async updateRole(
    id: string | number,
    role: Partial<Omit<Role, 'id' | 'createdAt' | 'updatedAt'>>
  ): Promise<ApiResponse<Role>> {
    try {
      await this.fetchCsrf();
      const payload = { ...role };
      return await this.client.put<Role>(`/api/v1/user-management/roles/${id}`, payload);
    } catch (error: unknown) {
      return this.handleError<Role>(error, 'ROLE_UPDATE_ERROR', 'Failed to update role');
    }
  }

  async deleteRole(id: string | number): Promise<ApiResponse<null>> {
    try {
      await this.fetchCsrf();
      return await this.client.delete<null>(`/api/v1/user-management/roles/${id}`);
    } catch (error: unknown) {
      return this.handleError<null>(error, 'ROLE_DELETE_ERROR', 'Failed to delete role');
    }
  }

  async paginate(
    search = '',
    searchFields: string[] = [],
    relations: string[] = [],
    filters: Record<string, string[]> = {},
    page = 1,
    perPage = 10
  ): Promise<ApiResponse<PaginatedApiResponse<Role[]>>> {
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

      return await this.client.get<PaginatedApiResponse<Role[]>>(`/api/v1/user-management/roles`, {
        params: parameters,
      });
    } catch (error: unknown) {
      return this.handleError<PaginatedApiResponse<Role[]>>(
        error,
        'ROLE_LIST_ERROR',
        'Failed to fetch roles'
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
