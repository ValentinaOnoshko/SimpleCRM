<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 py-10 px-6">
    <div class="max-w-7xl mx-auto">
      <div class="bg-white rounded-xl shadow-xl p-8">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Список сделок</h2>
        <div class="flex justify-center py-8" v-if="isLoading">
          <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500"></div>
        </div>
        <div v-else class="space-y-6">
          <div class="flex flex-wrap gap-4 mb-6">
            <select v-model="filters.status" @change="handleFilterChange" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="">Все статусы</option>
              <option v-for="status in dealStatuses" :key="status.value" :value="status.value">
                {{ status.label }}
              </option>
            </select>
            <select v-model="filters.sortBy" @change="handleFilterChange" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="created_at">по дате создания</option>
              <option value="creator">по создателю</option>
            </select>
            <select v-model="filters.sortOrder" @change="handleFilterChange" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="asc">по возрастанию</option>
              <option value="desc">по убыванию</option>
            </select>
          </div>
          <div class="flex gap-4 pt-4">
            <div class="relative flex-1">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Поиск по сделкам..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @input="handleSearch"
              />
              <button
                v-if="searchQuery"
                @click="clearSearch"
                class="absolute right-0 top-0 h-full flex items-center justify-center px-3 text-gray-400 hover:text-gray-600">
                ×
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import dealService from '@/services/dealService';

export default {
  data() {
    return {
      deals: [],
      isLoading: true,
      error: null,
      searchQuery: '',
      searchTimeout: null,
      filters: {
        status: '',
        sortBy: 'created_at',
        sortOrder: 'desc'
      }
    };
  },
  computed: {
    dealStatuses() {
      return Object.values(DealStatus);
    },
    isClient() {
      return this.isLocalStorageAvailable() ? localStorage.getItem('role') === 'client' : false;
    },
    profileLink() {
      if (!this.isLocalStorageAvailable()) return '/';
      const role = localStorage.getItem('role');
      const userId = localStorage.getItem('userId');
      return `/${role || 'client'}/${userId || '0'}/profile`;
    }
  },
  methods: {
    isLocalStorageAvailable() {
      try {
        const testKey = '__test__';
        localStorage.setItem(testKey, testKey);
        localStorage.removeItem(testKey);
        return true;
      } catch {
        return false;
      }
    },
    async fetchDeals() {
      this.isLoading = true;
      this.error = null;
      try {
        const params = {};

        if (this.filters.status) {
          params.status = this.filters.status;
        }

        if (this.filters.sortBy) {
          params.sort_by = this.filters.sortBy;
          params.order = this.filters.sortOrder;
        }

        if (this.searchQuery) {
          params.search = this.searchQuery;
        }

        const response = await dealService.getAll(params);
        this.deals = response;
      } catch (error) {
        console.error('Ошибка при загрузке сделок:', error);
        this.error = error.response?.data?.message || 'Ошибка при загрузке сделок';
      } finally {
        this.isLoading = false;
      }
    },
    handleSearch() {
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout);
      }
      this.searchTimeout = setTimeout(() => {
        this.fetchDeals();
      }, 300);
    },
    clearSearch() {
      this.searchQuery = '';
      this.fetchDeals();
    },
    handleFilterChange() {
      this.fetchDeals();
    }
  },
  mounted() {
    this.fetchDeals();
  }
};
</script>
