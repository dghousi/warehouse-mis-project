import { create } from 'zustand';
import { type User } from '../domain/entities/User';

type PaginationState = {
  page: number;
  perPage: number;
};
type UIState = {
  isLoading: boolean;
  isDrawerOpen: boolean;
  pagination: PaginationState;
  filters: Record<string, string[] | string>;
  selectedEntity: User | null;
  setLoading: (loading: boolean) => void;
  setDrawerOpen: (open: boolean) => void;
  setPagination: (pagination: PaginationState) => void;
  setFilters: (filters: Record<string, string[] | string>) => void;
  setSelectedEntity: (entity: User | null) => void;
};

export const useUIStore = create<UIState>((set) => ({
  filters: {},
  isDrawerOpen: false,
  isLoading: false,
  pagination: { page: 1, perPage: 10 },
  selectedEntity: null,
  setDrawerOpen: (open) => {
    set({ isDrawerOpen: open });
  },
  setFilters: (filters) => {
    set({ filters });
  },
  setLoading: (loading) => {
    set({ isLoading: loading });
  },
  setPagination: (pagination) => {
    set({ pagination });
  },
  setSelectedEntity: (entity) => {
    set({ selectedEntity: entity });
  },
}));

export const useUIStoreTest = create<UIState>((set) => ({
  filters: {},
  isDrawerOpen: false,
  isLoading: false,
  pagination: { page: 1, perPage: 10 },
  selectedEntity: null,
  setDrawerOpen: (open) => {
    set({ isDrawerOpen: open });
  },
  setFilters: (filters) => {
    set({ filters });
  },
  setLoading: (loading) => {
    set({ isLoading: loading });
  },
  setPagination: (pagination) => {
    set({ pagination });
  },
  setSelectedEntity: (entity) => {
    set({ selectedEntity: entity });
  },
}));
