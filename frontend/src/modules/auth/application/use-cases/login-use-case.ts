import { type User } from '@/modules/common/domain/entities/User';
import { type ApiResponse } from '@/types/ApiResponse';
import { AuthRepository } from '../../infrastructure/repositories/auth-repository';

export class LoginUseCase {
  constructor(private readonly authRepository: AuthRepository) {}

  async execute(email: string, password: string): Promise<ApiResponse<User>> {
    return this.authRepository.login(email, password);
  }
}
