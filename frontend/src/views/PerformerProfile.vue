<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 py-10 px-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-xl p-8">
      <h2 class="text-2xl font-semibold mb-6 text-gray-800">Профиль исполнителя</h2>
      <div class="flex items-center gap-6 mb-6">
        <div class="relative">
          <img
            :src="profile.getAvatar() || defaultAvatar"
            alt="Аватар"
            class="w-24 h-24 rounded-full object-cover border-2 border-gray-200"
          />
          <label class="absolute bottom-0 right-0 bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0118.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <input type="file" class="hidden" accept="image/*" @change="handleAvatarUpload" />
          </label>
        </div>
        <div class="flex-1">
          <p class="text-gray-600">
            <span class="font-semibold">ID:</span> {{ profile.getId() }}
          </p>
          <p class="text-gray-600">
            <span class="font-semibold">Имя:</span> {{ profile.getName() }}
          </p>
          <p class="text-gray-600">
            <span class="font-semibold">Email:</span> {{ profile.getEmail() }}
          </p>
          <p class="text-gray-600">
            <span class="font-semibold">Специализация:</span> {{ profile.getSpecialization() || 'Не указано' }}
          </p>
          <p class="text-gray-600">
            <span class="font-semibold">Дата регистрации:</span> {{ profile.getRegistrationDate() || 'Не указано' }}
          </p>
        </div>
      </div>
      <div class="flex gap-4 pt-4">
        <router-link to="/deals" class="flex-1 bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors">
          Мои сделки
        </router-link>
        <router-link to="/settings" class="flex-1 bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors">
          Редактировать профиль
        </router-link>
        <button @click="logout" class="flex-1 bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 transition-colors">
          Выйти
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import apiClient from '@/api/apiClient.js';
import defaultAvatar from '@/assets/images/default_avatar.png';

export default {
  data() {
    return {
      profile: {
        id: null,
        name: '',
        email: '',
        specialization: '',
        avatar: null,
        registrationDate: '',
        role: '',
        getId: () => null,
        getName: () => '',
        getEmail: () => '',
        getSpecialization: () => null,
        getAvatar: () => null,
        getRegistrationDate: () => '',
        getRole: () => '',
      },
      isLoading: true,
      error: null,
      defaultAvatar,
      tempAvatarUrl: null
    };
  },
  computed: {
    profileLink() {
      if (!this.isLocalStorageAvailable) return '/';
      const role = localStorage.getItem('role');
      const userId = localStorage.getItem('userId');
      return `/${role || 'performer'}/${userId || '0'}/profile`;
    },
    isLocalStorageAvailable() {
      try {
        const testKey = 'test';
        localStorage.setItem(testKey, testKey);
        localStorage.removeItem(testKey);
        return true;
      } catch {
        return false;
      }
    }
  },
  methods: {
    async fetchProfile() {
      this.isLoading = true;
      try {
        const response = await apiClient.get('/profile');
        this.profile = {
          ...response.data,
          getId: () => response.data.id,
          getName: () => response.data.name,
          getEmail: () => response.data.email,
          getSpecialization: () => response.data.specialization,
          getAvatar: () => response.data.avatar,
          getRegistrationDate: () => response.data.registrationDate,
          getRole: () => response.data.role,
        };
      } catch (error) {
        console.error('Ошибка при загрузке профиля:', error);
        this.error = 'Не удалось загрузить профиль';
      } finally {
        this.isLoading = false;
      }
    },
    async handleAvatarUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      this.tempAvatarUrl = URL.createObjectURL(file);
      this.profile.avatar = this.tempAvatarUrl;

      const formData = new FormData();
      formData.append('file', file);
      formData.append('type', 'avatar');

      try {
        const response = await apiClient.post('/files/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        this.profile.avatar = response.data.url;
        URL.revokeObjectURL(this.tempAvatarUrl);
        this.tempAvatarUrl = null;
        localStorage.removeItem('profile');
      } catch (err) {
        console.error('Ошибка загрузки аватара:', err);
        this.profile.avatar = this.defaultAvatar;
        alert('Ошибка при загрузке аватара');
      }
    },
    async logout() {
      try {
        await apiClient.post('/logout');
        localStorage.clear();
        this.$router.push('/login');
      } catch (error) {
        console.error('Ошибка при выходе:', error);
      }
    }
  },
  async mounted() {
    await this.fetchProfile();
  }
};
</script>
