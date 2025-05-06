<template>
  <div class="container">
    <h2 class="text-2xl font-semibold mb-4">Просмотр сделки {{ deal.id }}</h2>
    <div class="deal-info">
      <p>Заголовок: {{ deal.title }}</p>
      <p>Описание: {{ deal.description }}</p>
      <p>Дата создания: {{ deal.createdAt }}</p>
    </div>
    <router-link :to="'/deals/edit/' + deal.id" class="text-blue-500">Редактировать сделку</router-link>
  </div>
</template>

<script>
export default {
  data() {
    return {
      deal: {
        id: this.$route.params.id,
        title: 'Пример сделки',
        description: 'Описание сделки...',
        createdAt: '2025-05-06',
      },
    }
  },
}
</script>

<style scoped>
.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}
.deal-info p {
  margin: 10px 0;
}
</style>
