<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-100 via-white to-blue-100 px-4 py-12">
    <div class="mb-8 animate-fade-in">
      <div class="text-blue text-4xl font-bold mb-2 text-center">💼 CRM</div>
      <p class="text-blue text-sm text-center">Простая система управления проектами</p>
    </div>

    <div class="max-w-xl w-full bg-white rounded-xl shadow-xl p-8 text-center transform hover:shadow-2xl transition-all duration-500">
      <h1 class="text-2xl md:text-3xl font-bold mb-6">Добро пожаловать в CRM-систему</h1>

      <p class="text-gray-700 mb-4">
        Это учебный проект — простая CRM-система, разработанная с использованием Laravel, Vue.js, MySQL и Docker.
      </p>

      <p class="text-gray-700 mb-8">
        Предназначена для управления клиентами, задачами и проектами внутри небольших команд. Войдите или зарегистрируйтесь, чтобы начать использовать систему.
      </p>

      <div class="flex flex-col sm:flex-row gap-3 justify-center">
        <router-link
          v-if="isLoggedIn"
          :to="profileLink"
          class="px-6 py-2 bg-primary hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200"
        >
          Перейти в профиль
        </router-link>

        <router-link
          v-else
          :to="{ name: 'login' }"
          class="px-6 py-2 bg-primary hover:bg-indigo-700 text-white font-semibold rounded-lg transition duration-200"
        >
          Войти / Зарегистрироваться
        </router-link>
      </div>
    </div>

    <footer class="mt-8 text-blue text-sm opacity-80">
      &copy; {{ new Date().getFullYear() }} CRM System | Web-разработка
    </footer>
  </div>
</template>

<script>
export default {
  name: 'HomePage',
  data() {
    return {
      isLoggedIn: false,
      profileLink: null,
    };
  },
  mounted() {
    const token = localStorage.getItem('token');
    const role = localStorage.getItem('role');
    const userId = localStorage.getItem('userId');

    this.isLoggedIn = !!(token && role && userId);

    if (this.isLoggedIn) {
      this.profileLink = `/${role}/${userId}/profile`;
    }
  },
};
</script>

<style scoped>
.animate-fade-in {
  animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
