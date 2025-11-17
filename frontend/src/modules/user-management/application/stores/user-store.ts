import { type User } from '@/modules/common/domain/entities/User';
import { type PaginationMeta } from '@/types/ApiResponse';
import { create } from 'zustand';

type UserState = {
  users: User[];
  pagination: PaginationMeta | null;
  selectedUser: User | null;
  setUsers: (users: User[], pagination: PaginationMeta) => void;
  setSelectedUser: (user: User | null) => void;
  clearUsers: () => void;
};

export const useUserStore = create<UserState>((set) => ({
  clearUsers: () => {
    set({ pagination: null, selectedUser: null, users: [] });
  },
  pagination: null,
  selectedUser: null,
  setSelectedUser: (selectedUser) => {
    set({ selectedUser });
  },
  setUsers: (users, pagination) => {
    set({ pagination, users });
  },
  users: [],
}));
