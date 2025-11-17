export type ApiResponse<T> = {
  success: boolean;
  data: T | null;
  message?: string;
  error?: ApiErrorDetail;
};

export type ApiErrorDetail = {
  code: string;
  message: string;
  details: Record<string, string[]> | null;
};

export type PaginationLinks = {
  first: string;
  last: string;
  prev: string | null;
  next: string | null;
};

export type PaginationMeta = {
  currentPage: number;
  from: number;
  lastPage: number;
  path: string;
  perPage: number;
  to: number;
  total: number;
};

export type PaginatedApiResponse<T> = {
  data: T;
  meta: PaginationMeta;
  links: PaginationLinks;
};
