import type { ApiDataEnvelope } from '~/types/api/common';
import type { HomeContentDto } from '~/types/api/public-content';

export const useHomeContent = () => {
  const api = useApi();

  const fetchHome = async (): Promise<HomeContentDto> => {
    const response = await api.get<ApiDataEnvelope<HomeContentDto>>('/home');

    return response.data;
  };

  return {
    fetchHome,
  };
};
