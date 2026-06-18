import type { NitroFetchOptions, NitroFetchRequest } from 'nitropack/types';

type ApiRequestMethod = 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
type ApiFetchOptions = Omit<NitroFetchOptions<NitroFetchRequest>, 'baseURL' | 'headers' | 'method'> & {
  headers?: HeadersInit;
};

export const useApiBaseUrl = (): string => {
  const config = useRuntimeConfig();

  if (import.meta.server) {
    return config.apiInternalBase;
  }

  return config.public.apiBase;
};

export const useApi = () => {
  const baseURL = useApiBaseUrl();

  const request = async <T>(
    path: string,
    method: ApiRequestMethod,
    options: ApiFetchOptions = {},
  ): Promise<T> => {
    const headers = new Headers(options.headers);
    headers.set('Accept', 'application/json');

    return await $fetch<T>(path, {
      ...options,
      baseURL,
      headers,
      method,
    }) as T;
  };

  const get = async <T>(path: string, options: ApiFetchOptions = {}): Promise<T> => {
    return await request<T>(path, 'GET', options);
  };

  return {
    baseURL,
    get,
    request,
  };
};
