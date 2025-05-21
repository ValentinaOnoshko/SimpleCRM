<template>
  <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
    <div class="flex mb-6 border-b">
      <button
        @click="switchTab('login')"
        :class="tabClass('login')"
        class="w-1/2 py-2 text-center"
      >
        Вход
      </button>
      <button
        @click="switchTab('register')"
        :class="tabClass('register')"
        class="w-1/2 py-2 text-center"
      >
        Регистрация
      </button>
    </div>

    <form @submit.prevent="submitForm" class="space-y-5">
      <!-- Имя -->
      <div v-if="activeTab === 'register'">
        <label class="block mb-1 font-semibold">Имя</label>
        <input v-model="form.name" type="text" class="input" required />
        <p v-if="errors.name" class="text-red-500 text-sm">{{ errors.name[0] }}</p>
      </div>

      <!-- Email -->
      <div>
        <label class="block mb-1 font-semibold">Email</label>
        <input v-model="form.email" type="email" class="input" required />
        <p v-if="errors.email" class="text-red-500 text-sm">{{ errors.email[0] }}</p>
      </div>

      <!-- Пароль -->
      <div>
        <label class="block mb-1 font-semibold">Пароль</label>
        <input v-model="form.password" type="password" class="input" required />
        <div class="mt-2 space-y-1" v-if="activeTab === 'register'">
          <p :class="['text-xs', { 'text-green-500': form.password.length >= 8, 'text-red-500': form.password && form.password.length < 8 }]">
            • Минимум 8 символов
          </p>
          <p :class="['text-xs', { 'text-green-500': /[A-Z]/.test(form.password), 'text-red-500': form.password && !/[A-Z]/.test(form.password) }]">
            • Заглавная буква
          </p>
          <p :class="['text-xs', { 'text-green-500': /[a-z]/.test(form.password), 'text-red-500': form.password && !/[a-z]/.test(form.password) }]">
            • Строчная буква
          </p>
          <p :class="['text-xs', { 'text-green-500': /[0-9]/.test(form.password), 'text-red-500': form.password && !/[0-9]/.test(form.password) }]">
            • Цифра
          </p>
        </div>
        <p v-if="errors.password" class="text-red-500 text-sm mt-2">{{ errors.password[0] }}</p>
      </div>

      <!-- Подтверждение пароля -->
      <div v-if="activeTab === 'register'">
        <label class="block mb-1 font-semibold">Подтверждение пароля</label>
        <input v-model="form.password_confirmation" type="password" class="input" required />
        <p v-if="form.password !== form.password_confirmation" class="text-red-500 text-sm">Пароли не совпадают</p>
        <p v-if="errors.password_confirmation" class="text-red-500 text-sm">{{ errors.password_confirmation[0] }}</p>
      </div>

      <!-- Кнопка отправки -->
      <button
        type="submit"
        :class="[
          'w-full py-2 text-white rounded-lg transition',
          loading ? 'bg-gray-400 cursor-wait' : 'bg-primary hover:bg-indigo-700'
        ]"
        :disabled="loading"
      >
        {{ activeTab === 'register' ? 'Зарегистрироваться' : 'Войти' }}
      </button>

      <!-- Общие ошибки -->
      <div v-if="errors.general" class="text-red-500 text-sm mt-3">
        {{ errors.general }}
      </div>

      <!-- ВКонтакте -->
      <div class="text-center mt-4">
        <div id="vk-auth-widget"></div>
      </div>
    </form>
  </div>
</template>

<script>
import apiClient from '@/api/apiClient';
export default {
  name: 'AuthForm',
  props: {
    role: {
      type: String,
      default: 'client'
    },
    activeTab: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      form: {
        name: '',
        specialization: '',
        email: '',
        password: '',
        password_confirmation: ''
      },
      errors: {},
      loading: false
    };
  },
  computed: {
    isRegister() {
      return this.activeTab === 'register';
    },
    passwordValidation() {
      const password = this.form.password;
      return {
        length: password.length >= 8,
        hasUpper: /[A-Z]/.test(password),
        hasLower: /[a-z]/.test(password),
        hasDigit: /[0-9]/.test(password)
      };
    }
  },
  watch: {
    activeTab(newVal) {
      this.clearForm();
      this.$emit('update:activeTab', newVal);
    }
  },
  methods: {
    clearForm() {
      this.form = {
        name: '',
        specialization: '',
        email: '',
        password: '',
        password_confirmation: ''
      };
      this.errors = {};
    },
    tabClass(tab) {
      return this.activeTab === tab
        ? 'border-b-2 border-primary text-primary font-semibold'
        : 'text-gray-500';
    },
    switchTab(tab) {
      this.$emit('update:activeTab', tab);
      this.clearForm();
    },
    async submitForm() {
      this.errors = {};
      this.loading = true;

      // Проверка пароля при регистрации
      if (this.isRegister) {
        if (
          !this.passwordValidation.length ||
          !this.passwordValidation.hasUpper ||
          !this.passwordValidation.hasLower ||
          !this.passwordValidation.hasDigit
        ) {
          this.errors.password = ['Пароль не соответствует требованиям'];
          this.loading = false;
          return;
        }

        if (this.form.password !== this.form.password_confirmation) {
          this.errors.password_confirmation = ['Пароли не совпадают'];
          this.loading = false;
          return;
        }
      }

      const payload = {
        email: this.form.email,
        password: this.form.password
      };

      if (this.isRegister) {
        Object.assign(payload, {
          name: this.form.name,
          password_confirmation: this.form.password_confirmation,
          role: this.role,
          ...(this.role === 'performer' && { specialization: this.form.specialization })
        });
      }

      try {
        const { data } = this.isRegister
          ? await apiClient.post('/register', payload)
          : await apiClient.post('/login', payload);

        if (!data.token || !data.user) {
          throw new Error('Invalid response format');
        }

        localStorage.setItem('token', data.token);
        localStorage.setItem('role', data.user.role);
        localStorage.setItem('userId', data.user.id);

        // Перенаправление после авторизации
        await this.$router.push(
          data.user.role === 'performer'
            ? `/performer/${data.user.id}/profile`
            : `/client/${data.user.id}/profile`
        );
      } catch (error) {
        console.error('Auth error:', error);

        if (error.response?.status === 422) {
          this.errors = error.response.data.errors || { general: 'Ошибка валидации данных' };
        } else if (error.response?.status === 401) {
          this.errors.general = 'Неверные учетные данные';
        } else {
          this.errors.general = 'Произошла ошибка. Повторите попытку позже.';
        }
      } finally {
        this.loading = false;
      }
    },
    initVKID() {
      try {
        if (window.VkIdSdkInitialized) return;

        const { Config, OAuthList, WidgetEvents, OAuthListInternalEvents, Auth, ConfigResponseMode, ConfigSource } = window.VKIDSDK;

        Config.init({
          app: 53519146,
          redirectUrl: `${window.location.origin}/auth/vk/callback`,
          responseMode: ConfigResponseMode.Callback,
          source: ConfigSource.LOWCODE
        });

        const oAuth = new OAuthList();

        oAuth
          .mount({
            container: '#vk-auth-widget',
            oauthList: ['vkid', 'ok_ru', 'mail_ru']
          })
          .on(WidgetEvents.ERROR, (error) => {
            console.error('VKID error:', error);
            this.errors.general = 'Ошибка при авторизации через ВКонтакте';
          })
          .on(OAuthListInternalEvents.LOGIN_SUCCESS, (payload) => {
            Auth.exchangeCode(payload.code, payload.device_id)
              .then(this.handleVKAuth)
              .catch((error) => {
                console.error('Auth error:', error);
                this.errors.general = 'Ошибка при авторизации через ВКонтакте';
              });
          });

        window.VkIdSdkInitialized = true;
      } catch (error) {
        console.error('VKID initialization error:', error);
        this.errors.general = 'Ошибка инициализации ВКонтакте';
      }
    },
    async handleVKAuth(data) {
      this.loading = true;
      try {
        const response = await apiClient.post('/auth/vk/callback', {
          code: data.code,
          access_token: data.access_token,
          user_id: data.user_id,
          role: this.role
        });

        const result = response.data;

        if (result.need_email_update) {
          this.$emit('update:activeTab', 'register');
          this.form.email = '';
          this.errors.general = 'Пожалуйста, укажите ваш email для завершения регистрации';
          return;
        }

        localStorage.setItem('token', result.access_token);
        localStorage.setItem('role', result.user.role);
        localStorage.setItem('userId', result.user.id);

        await this.$router.push(
          result.user.role === 'performer'
            ? `/performer/${result.user.id}/profile`
            : `/client/${result.user.id}/profile`
        );
      } catch (error) {
        console.error('VK auth error:', error);
        this.errors.general = 'Ошибка при авторизации через ВКонтакте';
      } finally {
        this.loading = false;
      }
    }
  },
  mounted() {
    if (window.VKIDSDK) {
      this.initVKID();
    } else {
      const checkInterval = setInterval(() => {
        if (window.VKIDSDK) {
          clearInterval(checkInterval);
          this.initVKID();
        }
      }, 500);
      setTimeout(() => clearInterval(checkInterval), 5000);
    }
  }
};
</script>

<style scoped>
.input {
  @apply w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-primary;
}
</style>
