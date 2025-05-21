import apiClient from '@/api/apiClient.js';

const dealService = {
  getAll: async (params = {}) => {
    const cacheKey = `deals_${JSON.stringify(params)}`;
    const cached = localStorage.getItem(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const response = await apiClient.get('/deals', { params });
    const deals = response.data.data.map(deal => ({
      ...deal,
      creator: deal.creator || { getName: () => 'Не указан' }
    }));
    localStorage.setItem(cacheKey, JSON.stringify(deals));
    return deals;
  },
  get: async (dealId) => {
    const cacheKey = `deal_${dealId}`;
    const cached = localStorage.getItem(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const response = await apiClient.get(`/deals/${dealId}`);
    const deal = {
      ...response.data,
      creator: response.data.creator || { getName: () => 'Не указан' }
    };
    localStorage.setItem(cacheKey, JSON.stringify(deal));
    return deal;
  },
  create: async (dealData) => {
    const response = await apiClient.post('/deals', dealData);
    localStorage.removeItem('deals');
    return response.data;
  },
  updateStatus: async (dealId, status) => {
    await apiClient.put(`/deals/${dealId}/status`, { status });
    localStorage.removeItem('deals');
    localStorage.removeItem(`deal_${dealId}`);
  },
  getComments: async (dealId) => {
    const cacheKey = `comments_${dealId}`;
    const cached = localStorage.getItem(cacheKey);
    if (cached) {
      return JSON.parse(cached);
    }

    const response = await apiClient.get(`/deals/${dealId}/comments`);
    localStorage.setItem(cacheKey, JSON.stringify(response.data));
    return response.data;
  },
  addComment: async (dealId, formData) => {
    await apiClient.post(`/deals/${dealId}/comments`, formData);
    localStorage.removeItem(`comments_${dealId}`);
  }
};

export default dealService;
