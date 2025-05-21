<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-white to-blue-100 px-4 py-10">
    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-md p-8">
      <div class="mb-6">
        <router-link
          :to="profileLink"
          class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors whitespace-nowrap"
        >
          Вернуться в профиль
        </router-link>
      </div>
      <h1 class="text-2xl font-bold mb-6">Настройки аккаунта</h1>
      <div class="flex items-center space-x-4 mb-6">
        <img
          :src="avatarPreview || profile.getAvatar() || defaultAvatar"
          alt="Аватар"
          class="w-20 h-20 rounded-full object-cover border border-gray-300"
        />
        <div>
          <input type="file" @change="onAvatarChange" accept="image/*" />
          <p class="text-xs text-gray-500 mt-1">PNG или JPG, не больше 2MB</p>
          <p v-if="errors.avatar" class="text-red-500 text-sm mt-1">{{ errors.avatar }}</p>
        </div>
      </div>
      <form @submit.prevent="saveProfile" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Имя</label>
          <input
            v-model="profile.name"
            type="text"
            class="w-full border rounded-xl px-4 py-2"
            :class="{ 'border-red-500': errors.name }"
            required
          />
          <p v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="profile.email"
            type="email"
            class="w-full border rounded-xl px-4 py-2"
            :class="{ 'border-red-500': errors.email }"
            required
          />
          <p v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</p>
        </div>
        <div v-if="profile.getRole() === 'performer'">
          <label class="block text-sm font-medium text-gray-700 mb-1">Специализация</label>
          <input
            v-model="profile.specialization"
            type="text"
            class="w-full border rounded-xl px-4 py-2"
            :class="{ 'border-red-500': errors.specialization }"
            placeholder="Например: веб-разработка, дизайн..."
          />
          <p v-if="errors.specialization" class="text-red-500 text-sm mt-1">{{ errors.specialization }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Новый пароль</label>
          <input
            v-model="profile.password"
            type="password"
            class="w-full border rounded-xl px-4 py-2"
            :class="{ 'border-red-500': errors.password }"
            placeholder="Оставьте пустым, если не хотите менять"
          />
          <p v-if="errors.password" class="text-red-500 text-sm mt-1">{{ errors.password }}</p>
        </div>
        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 rounded-xl transition duration-200"
        >
          Сохранить
        </button>
        <p v-if="updateSuccess" class="text-green-600 text-sm mt-3 text-center">
          Профиль успешно обновлён!
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/apiClient.js';
import defaultAvatar from '@/assets/images/default_avatar.png';

export default {
  name: 'ProfileSettings',
  setup() {
    const router = useRouter();
    const profile = ref({
      name: '',
      email: '',
      password: '',
      specialization: '',
      role: '',
      id: null,
      avatar: null,
      getName: () => profile.value.name,
      getEmail: () => profile.value.email,
      getRole: () => profile.value.role,
      getSpecialization: () => profile.value.specialization,
      getAvatar: () => profile.value.avatar,
    });
    const avatarFile = ref(null);
    const avatarPreview = ref(null);
    const errors = ref({});
    const updateSuccess = ref(false);

    const loadProfile = async () => {
      try {
        const response = await apiClient.get('/profile');
        profile.value = {
          ...response.data,
          getName: () => response.data.name,
          getEmail: () => response.data.email,
          getRole: () => response.data.role,
          getSpecialization: () => response.data.specialization,
          getAvatar: () => response.data.avatar,
        };
      } catch (error) {
        console.error('Ошибка при загрузке профиля:', error);
        router.push('/');
      }
    };

    const onAvatarChange = (event) => {
      const file = event.target.files[0];
      if (!file) return;

      if (!file.type.startsWith('image/')) {
        errors.value.avatar = 'Файл должен быть изображением';
        return;
      }

      if (file.size > 2 * 1024 * 1024) {
        errors.value.avatar = 'Размер изображения не должен превышать 2MB';
        return;
      }

      errors.value.avatar = null;
      avatarFile.value = file;
      avatarPreview.value = URL.createObjectURL(file);
    };

    const validateForm = () => {
      errors.value = {};
      if (!profile.value.getName().trim()) errors.value.name = 'Имя обязательно';
      if (!profile.value.getEmail().trim()) {
        errors.value.email = 'Email обязателен';
      } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(profile.value.getEmail())) {
        errors.value.email = 'Неверный формат email';
      }
      if (profile.value.getRole() === 'performer' && !profile.value.getSpecialization()?.trim()) {
        errors.value.specialization = 'Укажите специализацию';
      }
      if (profile.value.password && profile.value.password.length < 6) {
        errors.value.password = 'Пароль должен быть не менее 6 символов';
      }
      return Object.keys(errors.value).length === 0;
    };

    const saveProfile = async () => {
      if (!validateForm()) return;

      const formData = new FormData();
      formData.append('name', profile.value.getName());
      formData.append('email', profile.value.getEmail());

      if (profile.value.getRole() === 'performer' && profile.value.getSpecialization()?.trim()) {
        formData.append('specialization', profile.value.getSpecialization());
      }

      if (profile.value.password) {
        formData.append('password', profile.value.password);
      }

      if (avatarFile.value) {
        formData.append('avatar', avatarFile.value);
      }

      try {
        await apiClient.post('/profile', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        profile.value.password = '';
        updateSuccess.value = true;

        setTimeout(() => (updateSuccess.value = false), 3000);

        const updatedProfile = await apiClient.get('/profile');

        if (this.isLocalStorageAvailable) {
          localStorage.setItem('role', updatedProfile.data.getRole());
          localStorage.setItem('userId', String(updatedProfile.data.getId()));
          localStorage.removeItem('profile');
        }

        await router.push(`/${updatedProfile.data.getRole()}/${updatedProfile.data.getId()}/profile`);
      } catch (error) {
        console.error('Ошибка при сохранении профиля:', error);
        if (error.response?.data) {
          errors.value = error.response.data.errors || {};
        }
      }
    };

    loadProfile();
    return {
      profile,
      avatarFile,
      avatarPreview,
      errors,
      updateSuccess,
      defaultAvatar,
      onAvatarChange,
      saveProfile
    };
  },
  computed: {
    profileLink() {
      if (!this.isLocalStorageAvailable) return '/';
      const role = localStorage.getItem('role');
      const userId = localStorage.getItem('userId');
      return `/${role || 'client'}/${userId || '0'}/profile`;
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
  }
};
</script>
