import { type User } from '@/modules/common/domain/entities/User';
import { AxiosClient } from '@/modules/common/infrastructure/api/axios-client';
import { type ApiResponse, type PaginatedApiResponse } from '@/types/ApiResponse';
import { AxiosRequestConfig } from 'axios';
import { type UserRepositoryInterface } from '../../domain/interfaces/UserRepositoryInterface';

export class UserRepository implements UserRepositoryInterface {
  private readonly client: AxiosClient;

  constructor() {
    this.client = new AxiosClient();
  }

  private async fetchCsrf(): Promise<void> {
    await this.client.get('/sanctum/csrf-cookie');
  }

  private hasNewFile(data: Record<string, any>): boolean {
    const fileFields = ['profilePhotoPath', 'userFormPath'];

    return fileFields.some((fieldName) => {
      const value = data[fieldName];
      return value instanceof File && value.size > 0 && value.lastModified > 0;
    });
  }

  private buildFormData(data: Record<string, any>): FormData {
    const formData = new FormData();

    for (const [key, value] of Object.entries(data)) {
      if (value === undefined) continue;

      if (this.shouldAppendNewFile(value)) {
        formData.append(key, value);
        continue;
      }

      if (value === null) {
        formData.append(key, '');
        continue;
      }

      if (this.shouldSkipHttpString(value)) {
        continue;
      }

      if (this.shouldSkipMockFile(value)) {
        continue;
      }

      if (value !== null) {
        formData.append(key, String(value));
      }
    }

    return formData;
  }

  private shouldAppendNewFile(value: any): boolean {
    return value instanceof File && value.size > 0 && value.lastModified > 0;
  }

  private shouldSkipHttpString(value: any): boolean {
    return typeof value === 'string' && value.startsWith('http');
  }

  private shouldSkipMockFile(value: any): boolean {
    return value instanceof File && value.size === 0;
  }

  private buildJson(data: Record<string, any>): Record<string, any> {
    const cleanedData: Record<string, any> = {};

    for (const [key, value] of Object.entries(data)) {
      if (this.isFileField(key)) {
        this.handleFileField(key, value, cleanedData);
        continue;
      }

      if (value !== undefined && value !== null && value !== '') {
        cleanedData[key] = value;
      }
    }

    return cleanedData;
  }

  private isFileField(key: string): boolean {
    return ['profilePhotoPath', 'userFormPath'].includes(key);
  }

  private handleFileField(key: string, value: any, cleanedData: Record<string, any>): void {
    if (value === null || value === '') {
      cleanedData[key] = '';
      return;
    }

    if (this.shouldSkipHttpString(value)) {
      return;
    }

    if (value instanceof File) {
      return;
    }
  }

  private buildPayloadAndConfig(data: Record<string, any>): {
    payload: FormData | Record<string, any>;
    config: AxiosRequestConfig;
  } {
    const cleanedData = { ...data };

    if (cleanedData.password === '') {
      cleanedData.password = undefined;
    }

    const hasFile = this.hasNewFile(cleanedData);

    if (hasFile) {
      const formData = this.buildFormData(cleanedData);
      return {
        config: { headers: { 'Content-Type': undefined } },
        payload: formData,
      };
    }

    const payload = this.buildJson(cleanedData);
    return { config: {}, payload };
  }

  async createUser(user: Omit<User, 'id' | 'createdAt' | 'updatedAt'>): Promise<ApiResponse<User>> {
    try {
      await this.fetchCsrf();
      const { config, payload } = this.buildPayloadAndConfig(user);
      return await this.client.post<User>('/api/v1/user-management/users', payload, config);
    } catch (error: unknown) {
      return this.handleError<User>(error, 'USER_CREATE_ERROR', 'Failed to create user');
    }
  }

  async getUser(id: string | number): Promise<ApiResponse<User>> {
    try {
      return await this.client.get<User>(`/api/v1/user-management/users/${id}`);
    } catch (error: unknown) {
      return this.handleError<User>(error, 'USER_FETCH_ERROR', 'Failed to fetch user');
    }
  }

  async updateUser(
    id: string | number,
    user: Partial<Omit<User, 'id' | 'createdAt' | 'updatedAt'>>
  ): Promise<ApiResponse<User>> {
    try {
      await this.fetchCsrf();
      const payload = { ...user };

      if (payload.password === '' || payload.password === null) {
        payload.password = undefined;
      }

      const { config, payload: finalPayload } = this.buildPayloadAndConfig(payload);

      if (finalPayload instanceof FormData) {
        finalPayload.append('_method', 'PUT');
        /* NOSONAR */ const headers = Object.assign({}, config.headers || {}, {
          'X-HTTP-Method-Override': 'PUT',
        });

        return await this.client.post<User>(`/api/v1/user-management/users/${id}`, finalPayload, {
          ...config,
          headers,
        });
      }

      return await this.client.put<User>(
        `/api/v1/user-management/users/${id}`,
        finalPayload,
        config
      );
    } catch (error: unknown) {
      return this.handleError<User>(error, 'USER_UPDATE_ERROR', 'Failed to update user');
    }
  }

  async deleteUser(id: string | number): Promise<ApiResponse<null>> {
    try {
      await this.fetchCsrf();
      return await this.client.delete<null>(`/api/v1/user-management/users/${id}`);
    } catch (error: unknown) {
      return this.handleError<null>(error, 'USER_DELETE_ERROR', 'Failed to delete user');
    }
  }

  async paginate(
    search = '',
    searchFields: string[] = [],
    relations: string[] = [],
    filters: Record<string, string[]> = {},
    page = 1,
    perPage = 10
  ): Promise<ApiResponse<PaginatedApiResponse<User[]>>> {
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

      return await this.client.get<PaginatedApiResponse<User[]>>(`/api/v1/user-management/users`, {
        params: parameters,
      });
    } catch (error: unknown) {
      return this.handleError<PaginatedApiResponse<User[]>>(
        error,
        'USER_LIST_ERROR',
        'Failed to fetch users'
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
