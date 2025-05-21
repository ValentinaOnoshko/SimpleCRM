<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-100 via-white to-blue-100 py-10 px-6">
    <div class="max-w-2xl mx-auto bg-white rounded-xl shadow-xl p-8">
      <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">Просмотр сделки</h2>
        <div class="flex gap-4">
          <router-link
            :to="profileLink"
            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors"
          >
            Профиль
          </router-link>
          <router-link
            v-if="isClient"
            :to="`/deals/${deal.getId()}/edit`"
            class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors"
          >
            Редактировать
          </router-link>
          <router-link
            :to="`/deals`"
            class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors"
          >
            Назад
          </router-link>
        </div>
      </div>
      <div v-if="isLoading" class="text-center py-8">
        <div class="animate-spin rounded-full h-12 w-12 border-t-4 border-blue-500"></div>
      </div>
      <div v-else-if="error" class="text-center py-8">
        <p class="text-red-500">{{ error }}</p>
      </div>
      <div v-else class="space-y-6">
        <div class="bg-gray-50 rounded-lg p-6 space-y-4">
          <div>
            <h3 class="text-lg font-medium text-gray-900">Название</h3>
            <p class="mt-1 text-gray-600">{{ deal.getTitle() }}</p>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">Описание</h3>
            <p class="mt-1 text-gray-600">{{ deal.getDescription() }}</p>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">Статус</h3>
            <p class="mt-1 text-gray-600">{{ deal.status.label }}</p>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">Дата создания</h3>
            <p class="mt-1 text-gray-600">{{ deal.getCreatedAt() || 'Не указано' }}</p>
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">Создатель</h3>
            <div class="mt-1 flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                {{ deal.creator?.getName()?.charAt(0).toUpperCase() || '?' }}
              </div>
              <p class="text-gray-600">{{ deal.creator?.getName() || 'Не указан' }}</p>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Комментарии</h3>
          <div class="space-y-4 mb-6 max-h-96 overflow-y-auto" ref="commentsContainer">
            <div v-if="comments.length === 0" class="text-center text-gray-500 py-4">Нет комментариев</div>
            <div v-for="comment in comments" :key="comment.getId()" class="flex gap-3">
              <div class="flex-shrink-0 w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                {{ comment.user_name?.charAt(0).toUpperCase() || '?' }}
              </div>
              <div class="flex-1">
                <div class="bg-white rounded-lg p-3 shadow-sm">
                  <div class="flex justify-between items-start mb-1">
                    <span class="font-medium text-gray-900">{{ comment.user_name }}</span>
                    <span class="text-sm text-gray-500">{{ formatDate(comment.getCreatedAt()) }}</span>
                  </div>
                  <p class="text-gray-700">{{ comment.getContent() }}</p>
                  <div v-if="comment.getFilePath()">
                    <a :href="comment.getFilePath()" target="_blank" class="text-blue-500 hover:underline">
                      {{ comment.getFileName() }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <form @submit.prevent="addComment" class="flex flex-col gap-2">
            <div class="flex gap-2">
              <textarea
                v-model="newComment"
                placeholder="Введите комментарий..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                rows="2"
                :disabled="isCommenting"
              ></textarea>
            </div>
            <div class="flex items-center gap-2">
              <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 01-2.828 0L3.172 10a2 2 0 010-2.828l6.586-6.586a2 2 0 012.828 0L15.172 7z" />
                </svg>
                <span>Прикрепить файл</span>
                <input
                  type="file"
                  class="hidden"
                  accept=".pdf,.docx,.txt,.jpg,.jpeg,.png"
                  @change="handleFileChange"
                  :disabled="isCommenting"
                />
              </label>
              <span v-if="selectedFile" class="text-sm text-gray-600">{{ selectedFile.name }}</span>
            </div>
            <button
              type="submit"
              class="bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition-colors"
              :disabled="isCommenting || !newComment.trim()"
            >
              {{ isCommenting ? 'Отправка...' : 'Отправить' }}
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import dealService from '@/services/dealService';

export default {
  props: {
    id: {
      type: [String, Number],
      required: true
    }
  },
  data() {
    return {
      deal: {
        id: null,
        title: '',
        description: '',
        status: { value: '', label: '' },
        created_at: '',
        creator: null,
        getId: () => null,
        getTitle: () => '',
        getDescription: () => '',
        getStatus: () => ({ value: '', label: '' }),
        getCreatedAt: () => '',
      },
      comments: [],
      newComment: '',
      selectedFile: null,
      isLoading: true,
      isCommenting: false,
      error: null,
    };
  },
  computed: {
    isClient() {
      return this.isLocalStorageAvailable ? localStorage.getItem('role') === 'client' : false;
    },
    profileLink() {
      if (!this.isLocalStorageAvailable) return '/';
      const role = localStorage.getItem('role');
      const userId = localStorage.getItem('userId');
      return `/${role || 'client'}/${userId || '0'}/profile`;
    },
    isLocalStorageAvailable() {
      try {
        const testKey = 'test';
        localStorage.setItem(testKey, testKey);
        localStorage.removeItem(testKey);
        return true;
      } catch {
        return false;
      }
    }
  },
  methods: {
    async fetchDeal() {
      try {
        const response = await dealService.get(this.id);
        this.deal = {
          ...response,
          getId: () => response.id,
          getTitle: () => response.title,
          getDescription: () => response.description,
          getStatus: () => response.status,
          getCreatedAt: () => response.created_at,
        };
        await this.fetchComments();
      } catch (error) {
        console.error('Ошибка при загрузке сделки:', error);
        this.error = error.response?.data?.message || 'Ошибка при загрузке сделки';
      } finally {
        this.isLoading = false;
      }
    },
    async fetchComments() {
      try {
        this.comments = (await dealService.getComments(this.id)).map(comment => ({
          ...comment,
          getId: () => comment.id,
          getContent: () => comment.content,
          getFilePath: () => comment.file_path,
          getFileName: () => comment.file_name,
          getCreatedAt: () => comment.created_at,
        }));
        this.$nextTick(() => {
          const container = this.$refs.commentsContainer;
          if (container) {
            container.scrollTop = container.scrollHeight;
          }
        });
      } catch (error) {
        console.error('Ошибка при загрузке комментариев:', error);
        this.error = error.response?.data?.message || 'Ошибка при загрузке комментариев';
      }
    },
    async addComment() {
      if (!this.newComment.trim()) return;
      this.isCommenting = true;
      try {
        const formData = new FormData();
        formData.append('content', this.newComment);
        if (this.selectedFile) {
          formData.append('file', this.selectedFile);
        }
        await dealService.addComment(this.id, formData);
        this.newComment = '';
        this.selectedFile = null;
        localStorage.removeItem(`comments_${this.id}`);
        await this.fetchComments();
      } catch (error) {
        console.error('Ошибка при добавлении комментария:', error);
        this.error = error.response?.data?.message || 'Ошибка при добавлении комментария';
      } finally {
        this.isCommenting = false;
      }
    },
    handleFileChange(event) {
      const file = event.target.files[0];
      if (!file) return;
      const allowedTypes = ['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain', 'image/jpeg', 'image/png'];
      if (!allowedTypes.includes(file.type)) {
        alert('Недопустимый тип файла. Разрешены: pdf, docx, txt, jpg, jpeg, png');
        return;
      }
      if (file.size > 10 * 1024 * 1024) {
        alert('Файл слишком большой. Максимальный размер: 10MB');
        return;
      }
      this.selectedFile = file;
    },
    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleString('ru-RU', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
  },
  async mounted() {
    await this.fetchDeal();
  }
};
</script>
