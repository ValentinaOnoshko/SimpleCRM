<template>
  <div class="container">
    <h2 class="text-2xl font-semibold mb-4">Профиль Исполнителя</h2>
    <div class="profile-info">
      <p>Имя: {{ profile.name }}</p>
      <p>Email: {{ profile.email }}</p>
      <p>Телефон: {{ profile.phone }}</p>
      <p>Дата регистрации: {{ profile.registrationDate }}</p>
      <p>Рейтинг: {{ profile.rating }}</p>
    </div>
    <router-link to="/settings" class="text-blue-500">Редактировать профиль</router-link>
  </div>
</template>

<script>
export default {
  data() {
    return {
      profile: {
        name: 'Петр Петров',
        email: 'petr@example.com',
        phone: '+7 999 234 56 78',
        registrationDate: '2021-03-15',
        rating: 4.8,
      },
    }
  },
}
</script>

<style scoped>
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}
.profile-info p {
  margin: 10px 0;
}
</style>
