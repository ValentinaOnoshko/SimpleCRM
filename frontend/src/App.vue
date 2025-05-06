<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100">
    <header class="bg-white shadow p-4 flex justify-between items-center">
      <h1 class="text-xl font-bold text-blue-600">SimpleCRM</h1>
      <nav class="space-x-4">
        <RouterLink class="text-gray-600 hover:text-blue-500" to="/deals">Сделки</RouterLink>
        <RouterLink class="text-gray-600 hover:text-blue-500" to="/client/profile">Профиль</RouterLink>
        <RouterLink class="text-gray-600 hover:text-blue-500" to="/settings">Настройки</RouterLink>
      </nav>
    </header>

    <main class="p-6">
      <div class="min-h-screen flex flex-col md:flex-row items-center
      justify-center gap-10 px-6 py-10 bg-gradient-to-br from-blue-100 via-white to-blue-100">
        <div class="w-full max-w-md">
          <div v-if="activeTab === 'register'" class="flex justify-center mb-6 gap-4">
            <button
              :class="buttonClass('client')"
              @click="role = 'client'"
            >Клиент</button>
            <button
              :class="buttonClass('performer')"
              @click="role = 'performer'"
            >Исполнитель</button>
          </div>

          <RegisterForm :role="role" v-model:activeTab="activeTab" @clearForm="clearForm" />
        </div>

        <div class="hidden md:block max-w-xl w-full">
          <img
            :src="regImage"
            alt="Auth illustration"
            class="w-full h-auto object-contain"
          />
        </div>
      </div>

      <RouterView />
    </main>
  </div>
</template>

<script>
import RegisterForm from './components/RegisterForm.vue'
import regImage from './assets/images/reg.png'
import { RouterLink, RouterView } from 'vue-router'

export default {
  components: {
    RegisterForm,
    RouterLink,
    RouterView
  },
  data() {
    return {
      role: 'client',
      activeTab: 'login',
      regImage,
    }
  },
  methods: {
    buttonClass(current) {
      return [
        'px-5 py-2 rounded-lg font-semibold transition-all',
        this.role === current
          ? 'bg-green-500 text-white'
          : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
      ]
    },
    clearForm() {
      this.role = 'client'
    },
  },
}
</script>
