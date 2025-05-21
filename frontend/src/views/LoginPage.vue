<template>
  <div class="min-h-screen flex flex-col md:flex-row items-center justify-center gap-10 px-6 py-10 bg-gradient-to-br from-blue-100 via-white to-blue-100">
    <div class="w-full max-w-md">
      <div v-if="activeTab === 'register'" class="flex justify-center mb-6 gap-4">
        <button :class="buttonClass('client')" @click="role = 'client'">Клиент</button>
        <button :class="buttonClass('performer')" @click="role = 'performer'">Исполнитель</button>
      </div>

      <RegisterForm :role="role" v-model:activeTab="activeTab" @clearForm="clearForm" />
    </div>

    <div class="hidden md:block max-w-xl w-full">
      <img :src="regImage" alt="Auth illustration" class="w-full h-auto object-contain" />
    </div>

    <LoadingSpinner v-if="isLoading" />
  </div>
</template>

<script>
import RegisterForm from '@/components/RegisterForm.vue';
import LoadingSpinner from '@/components/LoadingSpinner.vue';
import regImage from '@/assets/images/reg.png';

export default {
  name: 'LoginPage',
  components: { RegisterForm, LoadingSpinner },
  data() {
    return {
      role: 'client',
      activeTab: 'login',
      regImage,
      isLoading: false,
    };
  },
  methods: {
    buttonClass(current) {
      return [
        'px-5 py-2 rounded-lg font-semibold transition-all',
        this.role === current ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600 hover:bg-gray-300',
      ];
    },
    clearForm() {
      this.role = 'client';
      this.activeTab = 'login';
    },
  },
};
</script>
