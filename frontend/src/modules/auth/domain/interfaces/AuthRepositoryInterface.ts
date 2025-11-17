import { type User } from '@/modules/common/domain/entities/User';
import { type ApiResponse } from '@/types/ApiResponse';

export type AuthRepository = {
  login: (email: string, password: string) => Promise<ApiResponse<User>>;
  logout: () => Promise<ApiResponse<null>>;
  getCurrentUser: () => Promise<ApiResponse<User | null>>;
};
