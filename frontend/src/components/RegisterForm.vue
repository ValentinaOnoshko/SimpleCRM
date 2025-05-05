<template>
  <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
    <div class="flex mb-6 border-b">
      <button
        @click="$emit('update:activeTab', 'login'); clearForm()"
        :class="tabClass('login')"
        class="w-1/2 py-2 text-center"
      >
        Вход
      </button>
      <button
        @click="$emit('update:activeTab', 'register'); clearForm()"
        :class="tabClass('register')"
        class="w-1/2 py-2 text-center"
      >
        Регистрация
      </button>
    </div>

    <form @submit.prevent="submitForm" class="space-y-5">
      <div v-if="activeTab === 'register'">
        <label class="block mb-1 font-semibold">Имя</label>
        <input v-model="form.name" type="text" class="input" required />

        <div v-if="role === 'performer'" class="mt-4">
          <label class="block mb-1 font-semibold">Специализация</label>
          <input v-model="form.specialization" type="text" class="input" :required="role === 'performer'" />
        </div>
      </div>

      <div>
        <label class="block mb-1 font-semibold">Email</label>
        <input v-model="form.email" type="email" class="input" required />
      </div>

      <div>
        <label class="block mb-1 font-semibold">Пароль</label>
        <input v-model="form.password" type="password" class="input" required />
      </div>

      <button type="submit" class="w-full py-2 bg-primary text-white rounded-lg hover:bg-indigo-700 transition">
        {{ activeTab === 'register' ? 'Зарегистрироваться' : 'Войти' }}
      </button>

      <div class="text-center mt-4">
        <button type="button" @click="vkLogin">
          <img :src="vkLogo" alt="VK" class="w-10 h-10 mx-auto" />
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import axios from 'axios';
import vkLogo from '@/assets/images/vk_logo.png';

export default {
  props: {
    role: {
      type: String,
      default: 'client',
    },
    activeTab: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      form: {
        name: '',
        specialization: '',
        email: '',
        password: '',
      },
      vkLogo,
    };
  },
  methods: {
    tabClass(tab) {
      return this.activeTab === tab
        ? 'border-b-2 border-primary text-primary font-semibold'
        : 'text-gray-500';
    },
    async submitForm() {
      const payload = {
        ...this.form,
        role: this.role,
      };

      try {
        if (this.activeTab === 'register') {
          await axios.post('/api/register', payload);
          alert('Регистрация прошла успешно!');
        } else {
          const response = await axios.post('/api/login', payload);
          localStorage.setItem('token', response.data.token);
          alert('Вход выполнен!');
        }

        this.clearForm();
      } catch (error) {
        console.error(error);
        alert('Ошибка: ' + (error.response?.data?.message || 'Что-то пошло не так'));
      }
    },
    vkLogin() {
      window.location.href = '/auth/vk/redirect';
    },
    clearForm() {
      this.form = {
        name: '',
        specialization: '',
        email: '',
        password: '',
      };
    },
  },
};
</script>

<style scoped>
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-primary;
}
</style>
