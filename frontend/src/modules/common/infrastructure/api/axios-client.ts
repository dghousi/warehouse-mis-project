import { API_ORIGIN } from '@/config/environment';
import { type ApiResponse } from '@/types/ApiResponse';
import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios';
import Cookies from 'js-cookie';

export class AxiosClient {
  private readonly axios: AxiosInstance;

  constructor(baseURL: string = API_ORIGIN) {
    this.axios = axios.create({
      baseURL,
      headers: { 'Content-Type': 'application/json' },
      withCredentials: true,
    });

    this.axios.interceptors.request.use((config) => {
      const token = Cookies.get('XSRF-TOKEN');
      if (token) {
        config.headers['X-XSRF-TOKEN'] = decodeURIComponent(token);
      }
      return config;
    });
  }

  async get<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    const response = await this.axios.get<ApiResponse<T>>(url, config);
    return response.data;
  }

  async post<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    const response = await this.axios.post<ApiResponse<T>>(url, data, config);
    return response.data;
  }

  async put<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    const response = await this.axios.put<ApiResponse<T>>(url, data, config);
    return response.data;
  }

  async delete<T>(url: string, config?: AxiosRequestConfig): Promise<ApiResponse<T>> {
    const response = await this.axios.delete<ApiResponse<T>>(url, config);
    return response.data;
  }
}
