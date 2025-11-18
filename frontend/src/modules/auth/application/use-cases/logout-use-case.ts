import { type ApiResponse } from '@/types/ApiResponse';
import { AuthRepository } from '../../infrastructure/repositories/auth-repository';

export class LogoutUseCase {
  constructor(private readonly authRepository: AuthRepository) {}

  async execute(): Promise<ApiResponse<null>> {
    return this.authRepository.logout();
  }
}
