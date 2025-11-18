import { type User } from '@/modules/common/domain/entities/User';
import { create } from 'zustand';

type AuthState = {
  user: User | null;
  isSessionChecked: boolean;
  setUser: (user: User | null) => void;
  markSessionChecked: () => void;
};

export const useAuthStore = create<AuthState>((set) => ({
  isSessionChecked: false,
  markSessionChecked: () => {
    set({ isSessionChecked: true });
  },
  setUser: (user) => {
    set({ user });
  },
  user: null,
}));
