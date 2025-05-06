<template>
  <div class="container">
    <h2 class="text-2xl font-semibold mb-4">Настройки аккаунта</h2>
    <form>
      <div class="mb-4">
        <label for="name" class="block text-gray-700">Имя</label>
        <input type="text" id="name" class="w-full border p-2 rounded-lg" />
      </div>
      <div class="mb-4">
        <label for="email" class="block text-gray-700">Email</label>
        <input type="email" id="email" class="w-full border p-2 rounded-lg" />
      </div>
      <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg">Сохранить изменения</button>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {}
  },
}
</script>

<style scoped>
.container {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}
</style>
