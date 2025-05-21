<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 flex items-center justify-center p-6">
    <div class="text-center">
      <img
        src="@/assets/images/404.png"
        alt="404 Not Found"
        class="w-[90vw] max-w-4xl mx-auto mb-8"
      />
      <button
        @click="goBack"
        class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-600 transition-colors"
      >
        Вернуться назад
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: 'NotFound',
  methods: {
    goBack() {
      const token = localStorage.getItem('token');
      const role = localStorage.getItem('role');
      const userId = localStorage.getItem('userId');

      if (token && role && userId) {
        // Если пользователь авторизован, возвращаем на страницу профиля
        this.$router.push(role === 'performer' ? `/performer/${userId}/profile` : `/client/${userId}/profile`);
      } else {
        // Если не авторизован, возвращаем на главную
        this.$router.push('/');
      }
    }
  }
}
</script>
