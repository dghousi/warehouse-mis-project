import { type User } from '@/modules/common/domain/entities/User';
import { type UserRepositoryInterface } from '@/modules/user-management/domain/interfaces/UserRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class GetUserUseCase {
  constructor(private readonly userRepository: UserRepositoryInterface) {}

  async execute(id: string | number): Promise<ApiResponse<User>> {
    return this.userRepository.getUser(id);
  }
}
