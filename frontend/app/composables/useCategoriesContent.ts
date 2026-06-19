import type { ApiDataEnvelope } from '~/types/api/common';
import type { CategoriesPageDto } from '~/types/api/public-content';

export const useCategoriesContent = () => {
  const api = useApi();

  const fetchCategories = async (): Promise<CategoriesPageDto> => {
    const response = await api.get<ApiDataEnvelope<CategoriesPageDto>>('/categories');

    return response.data;
  };

  return {
    fetchCategories,
  };
};
