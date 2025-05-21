<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 py-10 px-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-xl p-8">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Редактирование сделки</h2>
      <form @submit.prevent="saveDeal" class="space-y-6">
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
          >
            {{ deal.description }}
          </textarea>
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
            class="flex-1 bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Сохранение...' : 'Сохранить'}}

          <router-link
            :to="profileLink"
            class="flex-1 bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors"
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
import dealService from '@/services/dealService';
import { DealStatus } from '@/enums/DealStatus';

export default {
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      deal: {
        title: '',
        description: '',
        status: '',
      },
      dealStatuses: Object.values(DealStatus),
      isLoading: false,
      error: null,
      defaultAvatar: require('@/assets/images/default_avatar.png').default
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
    async fetchDeal() {
      try {
        const response = await dealService.get(this.id);
        this.deal = response;
      } catch (error) {
        console.error('Ошибка при загрузке сделки:', error);
        this.error = error.response?.data?.message || 'Ошибка при загрузке сделки';
      } finally {
        this.isLoading = false;
      }
    },
    async saveDeal() {
      this.isLoading = true;
      try {
        const formData = new FormData();
        formData.append('title', this.deal.title);
        formData.append('description', this.deal.description);
        formData.append('status', this.deal.status);
        formData.append('id', String(localStorage.getItem('userId')));

        await dealService.update(this.id, formData);
        this.$router.push(`/deals/${this.id}`);
      } catch (error) {
        console.error('Ошибка при сохранении сделки:', error);
        this.error = 'Ошибка при сохранении сделки';
      } finally {
        this.isLoading = false;
      }
    }
  },
  async mounted() {
    await this.fetchDeal();
  }
};
</script>
