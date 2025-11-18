import { type User } from '@/modules/common/domain/entities/User';
import { type UserRepositoryInterface } from '@/modules/user-management/domain/interfaces/UserRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class UpdateUserUseCase {
  constructor(private readonly userRepository: UserRepositoryInterface) {}

  async execute(
    id: string | number,
    user: Partial<Omit<User, 'id' | 'createdAt' | 'updatedAt'>>
  ): Promise<ApiResponse<User>> {
    return await this.userRepository.updateUser(id, user);
  }
}
