<template>
  <div class="container">
    <h2 class="text-2xl font-semibold mb-4">Список сделок</h2>
    <div class="deal-list">
      <div class="deal-item" v-for="deal in deals" :key="deal.id">
        <p>{{ deal.title }}</p>
        <router-link :to="'/deals/' + deal.id" class="text-blue-500">Посмотреть</router-link>
        <router-link :to="'/deals/edit/' + deal.id" class="text-blue-500 ml-4">Редактировать</router-link>
      </div>
    </div>
    <router-link to="/deals/edit/new" class="bg-green-500 text-white px-4 py-2 rounded-lg mt-4 inline-block">Создать новую сделку</router-link>
  </div>
</template>

<script>
export default {
  data() {
    return {
      deals: [
        { id: 1, title: 'Сделка 1' },
        { id: 2, title: 'Сделка 2' },
        { id: 3, title: 'Сделка 3' },
      ],
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
.deal-item {
  margin: 15px 0;
}
</style>
