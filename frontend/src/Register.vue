<template>
  <div>
    <div class="tabs">
      <button @click="setRole('client')">Регистрация клиента</button>
      <button @click="setRole('executor')">Регистрация исполнителя</button>
    </div>

    <form @submit.prevent="register">
      <input v-model="name" type="text" placeholder="Имя" required />
      <input v-model="email" type="email" placeholder="Электронная почта" required />
      <input v-model="password" type="password" placeholder="Пароль" required />
      <button type="submit">Зарегистрироваться</button>
    </form>
  </div>
</template>

<script>
import api from '../api';

export default {
  data() {
    return {
      name: '',
      email: '',
      password: '',
      role: 'client',
    };
  },
  methods: {
    setRole(role) {
      this.role = role;
    },
    async register() {
      try {
        const response = await api.post('register', {
          name: this.name,
          email: this.email,
          password: this.password,
          role: this.role,
        });

        localStorage.setItem('token', response.data.token);
        console.log('Registration successful:', response.data);
      } catch (error) {
        console.error('Registration error:', error);
      }
    },
  },
};
</script>
