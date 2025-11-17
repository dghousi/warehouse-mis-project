import { type PaginationMeta } from '@/types/ApiResponse';
import { create } from 'zustand';
import { type Role } from '../../domain/entities/Role';

type RoleState = {
  roles: Role[];
  pagination: PaginationMeta | null;
  selectedRole: Role | null;
  setRoles: (roles: Role[], pagination: PaginationMeta) => void;
  setSelectedRole: (role: Role | null) => void;
  clearRoles: () => void;
};

export const useRoleStore = create<RoleState>((set) => ({
  clearRoles: () => {
    set({ pagination: null, roles: [], selectedRole: null });
  },
  pagination: null,
  roles: [],
  selectedRole: null,
  setRoles: (roles, pagination) => {
    set({ pagination, roles });
  },
  setSelectedRole: (selectedRole) => {
    set({ selectedRole });
  },
}));
