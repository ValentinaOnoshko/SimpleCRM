<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 py-10 px-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-xl p-8">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Профиль Клиента</h2>
      <div v-if="isLoading" class="text-center py-8">
        <p class="text-gray-600">Загрузка профиля...</p>
      </div>
      <div v-else-if="error" class="text-center py-8">
        <p class="text-red-500">{{ error }}</p>
      </div>
      <div v-else class="space-y-6">
        <div class="bg-gray-50 rounded-lg p-6 space-y-4">
          <div class="flex items-center gap-6">
            <div class="relative">
              <img
                :src="profile.avatar || defaultAvatar"
                alt="Аватар"
                class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
              />
              <label class="absolute bottom-0 right-0 bg-blue-500 text-white p-2 rounded-full cursor-pointer hover:bg-blue-600 transition-colors" title="Изменить фото">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <input type="file" class="hidden" accept="image/jpeg,image/png" @change="handleAvatarUpload" />
              </label>
            </div>
            <div class="flex-1">
              <p class="text-gray-600"><span class="font-semibold">ID:</span> {{ profile.id }}</p>
              <p class="text-gray-600"><span class="font-semibold">Имя:</span> {{ profile.name }}</p>
              <p class="text-gray-600"><span class="font-semibold">Email:</span> {{ profile.email }}</p>
              <p class="text-gray-600">
                <span class="font-semibold">Дата регистрации:</span> {{ profile.registrationDate || 'Не указано' }}
              </p>
            </div>
          </div>
        </div>
        <div class="flex gap-4 pt-4">
          <router-link to="/deals/create" class="flex-1 bg-green-500 text-white px-6 py-3 rounded-lg text-center font-semibold hover:bg-green-600 transition-colors flex items-center justify-center">
            Создать сделку
          </router-link>
          <router-link to="/deals" class="flex-1 bg-blue-500 text-white px-6 py-3 rounded-lg text-center font-semibold hover:bg-blue-600 transition-colors flex items-center justify-center">
            Мои сделки
          </router-link>
          <router-link to="/settings" class="flex-1 bg-gray-500 text-white px-6 py-3 rounded-lg text-center font-semibold hover:bg-gray-600 transition-colors flex items-center justify-center">
            Редактировать профиль
          </router-link>
          <button
            @click="logout"
            class="flex-1 bg-red-500 text-white px-6 py-3 rounded-lg text-center font-semibold hover:bg-red-600 transition-colors flex items-center justify-center"
          >
            Выйти
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from '@/api/apiClient.js';
import defaultAvatar from '@/assets/images/default_avatar.png';

export default {
  name: 'ClientProfile',
  props: {
    userId: {
      type: [String, Number],
      required: true,
    },
  },
  data() {
    return {
      profile: null,
      isLoading: true,
      error: null,
      defaultAvatar,
    };
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
    async handleAvatarUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      if (!file.type.startsWith('image/')) {
        alert('Пожалуйста, выберите изображение');
        return;
      }

      const formData = new FormData();
      formData.append('file', file);
      formData.append('type', 'avatar');

      try {
        const response = await apiClient.post('/files/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        this.profile.avatar = response.data.path;
      } catch (err) {
        console.error('Ошибка загрузки аватара:', err);
        alert('Ошибка при загрузке аватара');
      }
    },
    async fetchProfile() {
      try {
        if (this.$route.meta?.profile) {
          this.profile = this.$route.meta.profile;
          return;
        }

        const token = localStorage.getItem('token');
        if (!token) {
          this.error = 'Нет токена авторизации';
          this.isLoading = false;
          this.$router.push('/');
          return;
        }

        const {data} = await apiClient.get('/profile');

        if (!data.id && this.userId) {
          data.id = parseInt(this.userId);
        }

        this.profile = data;
      } catch (err) {
        console.error('Ошибка загрузки профиля:', err);
        this.error = 'Не удалось загрузить данные профиля';
      } finally {
        this.isLoading = false;
      }
    },
    logout() {
      if (this.isLocalStorageAvailable()) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        localStorage.removeItem('userId');
      }
      this.$router.push('/login');
    }
  },
  mounted() {
    this.fetchProfile();
  },
};
</script>
