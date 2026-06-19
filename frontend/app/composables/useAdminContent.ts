import type { ApiDataEnvelope } from '~/types/api/common';
import type {
  AdminAppealDto,
  AdminAppealListDto,
  AdminAppealPayload,
  AdminAdvertisementDto,
  AdminAdvertisementListDto,
  AdminAdvertisementPayload,
  AdminCategoriesDto,
  AdminCategoryDto,
  AdminCategoryPayload,
  AdminHomepageSlideDto,
  AdminHomepageSlideListDto,
  AdminHomepageSlidePayload,
  AdminNewsDto,
  AdminNewsListDto,
  AdminNewsPayload,
} from '~/types/api/admin-content';

export const useAdminContent = () => {
  const api = useApi();
  const auth = useAuth();

  const headers = (): HeadersInit => auth.authHeaders();

  const listCategories = async (): Promise<AdminCategoriesDto> => {
    const response = await api.get<ApiDataEnvelope<AdminCategoriesDto>>('/admin/categories', {
      headers: headers(),
    });

    return response.data;
  };

  const createCategory = async (payload: AdminCategoryPayload): Promise<AdminCategoryDto> => {
    const response = await api.request<ApiDataEnvelope<AdminCategoryDto>>('/admin/categories', 'POST', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const updateCategory = async (id: string, payload: AdminCategoryPayload): Promise<AdminCategoryDto> => {
    const response = await api.request<ApiDataEnvelope<AdminCategoryDto>>(`/admin/categories/${id}`, 'PATCH', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const deleteCategory = async (id: string): Promise<void> => {
    await api.request(`/admin/categories/${id}`, 'DELETE', {
      headers: headers(),
    });
  };

  const listNews = async (): Promise<AdminNewsListDto> => {
    const response = await api.get<ApiDataEnvelope<AdminNewsListDto>>('/admin/news', {
      headers: headers(),
    });

    return response.data;
  };

  const listHomepageSlides = async (): Promise<AdminHomepageSlideListDto> => {
    const response = await api.get<ApiDataEnvelope<AdminHomepageSlideListDto>>('/admin/homepage-slides', {
      headers: headers(),
    });

    return response.data;
  };

  const createHomepageSlide = async (payload: AdminHomepageSlidePayload): Promise<AdminHomepageSlideDto> => {
    const response = await api.request<ApiDataEnvelope<AdminHomepageSlideDto>>('/admin/homepage-slides', 'POST', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const updateHomepageSlide = async (id: string, payload: AdminHomepageSlidePayload): Promise<AdminHomepageSlideDto> => {
    const response = await api.request<ApiDataEnvelope<AdminHomepageSlideDto>>(`/admin/homepage-slides/${id}`, 'PATCH', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const deleteHomepageSlide = async (id: string): Promise<void> => {
    await api.request(`/admin/homepage-slides/${id}`, 'DELETE', {
      headers: headers(),
    });
  };

  const listAdvertisements = async (): Promise<AdminAdvertisementListDto> => {
    const response = await api.get<ApiDataEnvelope<AdminAdvertisementListDto>>('/admin/advertisements', {
      headers: headers(),
    });

    return response.data;
  };

  const createAdvertisement = async (payload: AdminAdvertisementPayload): Promise<AdminAdvertisementDto> => {
    const response = await api.request<ApiDataEnvelope<AdminAdvertisementDto>>('/admin/advertisements', 'POST', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const updateAdvertisement = async (id: string, payload: AdminAdvertisementPayload): Promise<AdminAdvertisementDto> => {
    const response = await api.request<ApiDataEnvelope<AdminAdvertisementDto>>(`/admin/advertisements/${id}`, 'PATCH', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const deleteAdvertisement = async (id: string): Promise<void> => {
    await api.request(`/admin/advertisements/${id}`, 'DELETE', {
      headers: headers(),
    });
  };

  const createNews = async (payload: AdminNewsPayload): Promise<AdminNewsDto> => {
    const response = await api.request<ApiDataEnvelope<AdminNewsDto>>('/admin/news', 'POST', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const updateNews = async (id: string, payload: AdminNewsPayload): Promise<AdminNewsDto> => {
    const response = await api.request<ApiDataEnvelope<AdminNewsDto>>(`/admin/news/${id}`, 'PATCH', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const deleteNews = async (id: string): Promise<void> => {
    await api.request(`/admin/news/${id}`, 'DELETE', {
      headers: headers(),
    });
  };

  const listAppeals = async (): Promise<AdminAppealListDto> => {
    const response = await api.get<ApiDataEnvelope<AdminAppealListDto>>('/admin/appeals', {
      headers: headers(),
    });

    return response.data;
  };

  const createAppeal = async (payload: AdminAppealPayload): Promise<AdminAppealDto> => {
    const response = await api.request<ApiDataEnvelope<AdminAppealDto>>('/admin/appeals', 'POST', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const updateAppeal = async (id: string, payload: AdminAppealPayload): Promise<AdminAppealDto> => {
    const response = await api.request<ApiDataEnvelope<AdminAppealDto>>(`/admin/appeals/${id}`, 'PATCH', {
      body: payload,
      headers: headers(),
    });

    return response.data;
  };

  const deleteAppeal = async (id: string): Promise<void> => {
    await api.request(`/admin/appeals/${id}`, 'DELETE', {
      headers: headers(),
    });
  };

  return {
    createAdvertisement,
    createAppeal,
    createCategory,
    createHomepageSlide,
    createNews,
    deleteAdvertisement,
    deleteAppeal,
    deleteCategory,
    deleteHomepageSlide,
    deleteNews,
    listAdvertisements,
    listAppeals,
    listCategories,
    listHomepageSlides,
    listNews,
    updateAdvertisement,
    updateAppeal,
    updateCategory,
    updateHomepageSlide,
    updateNews,
  };
};
