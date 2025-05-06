<template>
  <div class="container">
    <h2 class="text-2xl font-semibold mb-4">Редактирование сделки {{ deal.id }}</h2>
    <form>
      <div class="mb-4">
        <label for="title" class="block text-gray-700">Заголовок</label>
        <input type="text" id="title" v-model="deal.title" class="w-full border p-2 rounded-lg" />
      </div>
      <div class="mb-4">
        <label for="description" class="block text-gray-700">Описание</label>
        <textarea id="description" v-model="deal.description" class="w-full border p-2 rounded-lg"></textarea>
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Сохранить</button>
    </form>
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
</style>
