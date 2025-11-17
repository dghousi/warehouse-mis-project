import { type UserRepositoryInterface } from '@/modules/user-management/domain/interfaces/UserRepositoryInterface';
import { type ApiResponse } from '@/types/ApiResponse';

export class DeleteUserUseCase {
  constructor(private readonly userRepository: UserRepositoryInterface) {}

  async execute(id: string | number): Promise<ApiResponse<null>> {
    return await this.userRepository.deleteUser(id);
  }
}
