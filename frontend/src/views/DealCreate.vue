<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 py-10 px-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-xl p-8">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Создание сделки</h2>
      <form @submit.prevent="submitForm" class="space-y-6">
        <div>
          <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Название</label>
          <input
            v-model="deal.title"
            id="title"
            type="text"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            required
          />
        </div>
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
          <textarea
            v-model="deal.description"
            id="description"
            rows="4"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            required
          >{{ deal.description }}</textarea>
        </div>
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Статус</label>
          <select
            v-model="deal.status"
            id="status"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            required
          >
            <option v-for="status in dealStatuses" :key="status.value" :value="status.value">
              {{ status.label }}
            </option>
          </select>
        </div>
        <div class="flex gap-4 pt-4">
          <button
            type="submit"
            class="flex-1 bg-blue-500 text-white px-6 py-3 rounded-lg text-center font-semibold hover:bg-blue-600 transition-colors flex items-center justify-center"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Создание...' : 'Создать сделку' }}
            <router-link
              :to="profileLink"
              class="flex-1 bg-gray-500 text-white px-6 py-3 rounded-lg text-center font-semibold hover:bg-gray-600 transition-colors flex items-center justify-center"
            >
              Отмена
            </router-link>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script>
import { DealStatus } from '@/enums/DealStatus';
import dealService from '@/services/dealService';

export default {
  data() {
    return {
      deal: {
        title: '',
        description: '',
        status: DealStatus.NEW.value,
      },
      dealStatuses: Object.values(DealStatus),
      isLoading: false,
    };
  },
  computed: {
    profileLink() {
      if (!this.isLocalStorageAvailable()) return '/';
      const role = localStorage.getItem('role');
      const userId = localStorage.getItem('userId');
      return `/${role || 'client'}/${userId || '0'}/profile`;
    },
    isLocalStorageAvailable() {
      try {
        const testKey = '__test__';
        localStorage.setItem(testKey, testKey);
        localStorage.removeItem(testKey);
        return true;
      } catch {
        return false;
      }
    }
  },
  methods: {
    async submitForm() {
      this.isLoading = true;
      try {
        const formData = new FormData();
        formData.append('title', this.deal.title);
        formData.append('description', this.deal.description);
        formData.append('status', this.deal.status);
        formData.append('client_id', String(localStorage.getItem('userId')));

        await dealService.create(formData);
      } catch (error) {
        console.error('Ошибка при создании сделки:', error);
        alert(error.response?.data?.message || 'Ошибка при создании сделки');
      } finally {
        this.isLoading = false;
      }
    }
  }
};
</script>
