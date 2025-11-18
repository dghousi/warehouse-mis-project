import { type AuthRepository as AuthRepositoryInterface } from '@/modules/auth/domain/interfaces/AuthRepositoryInterface';
import { type User } from '@/modules/common/domain/entities/User';
import { AxiosClient } from '@/modules/common/infrastructure/api/axios-client';
import { parseApiError } from '@/modules/common/utils/http-error-utilities';
import { type ApiResponse } from '@/types/ApiResponse';

export class AuthRepository implements AuthRepositoryInterface {
  private readonly client = new AxiosClient();

  private async fetchCsrf(): Promise<void> {
    await this.client.get('/sanctum/csrf-cookie');
  }

  async login(email: string, password: string): Promise<ApiResponse<User>> {
    try {
      await this.fetchCsrf();
      await this.client.post('/api/auth/login', { email, password });
      return await this.client.get<User>('/api/me');
    } catch (error) {
      return {
        data: null,
        error: parseApiError(error),
        message: parseApiError(error).message,
        success: false,
      };
    }
  }

  async logout(): Promise<ApiResponse<null>> {
    try {
      await this.fetchCsrf();
      return await this.client.post<null>('/api/auth/logout', {});
    } catch (error) {
      return {
        data: null,
        error: parseApiError(error),
        message: parseApiError(error).message,
        success: false,
      };
    }
  }

  async getCurrentUser(): Promise<ApiResponse<User | null>> {
    try {
      return await this.client.get<User | null>('/api/me');
    } catch (error: any) {
      if (error?.response?.status === 401) {
        return { data: null, message: 'Unauthenticated', success: true };
      }
      return {
        data: null,
        error: parseApiError(error),
        message: parseApiError(error).message,
        success: false,
      };
    }
  }
}
