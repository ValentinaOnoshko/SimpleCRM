<template>
  <div class="file-upload">
    <div
      class="upload-area"
      :class="{ 'is-dragover': isDragover }"
      @dragenter.prevent="isDragover = true"
      @dragleave.prevent="isDragover = false"
      @dragover.prevent
      @drop.prevent="handleDrop"
    >
      <input
        type="file"
        ref="fileInput"
        class="hidden"
        @change="handleFileSelect"
        :accept="accept"
      />
      <div class="upload-content">
        <svg
          class="w-12 h-12 text-gray-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
          />
        </svg>
        <p class="mt-2 text-sm text-gray-600">
          {{ isDragover ? 'Отпустите файл здесь' : 'Перетащите файл сюда или нажмите для выбора' }}
        </p>
        <p class="text-xs text-gray-500 mt-1">
          Максимальный размер: 10MB
        </p>
      </div>
    </div>

    <div v-if="error" class="mt-2 text-sm text-red-600">
      {{ error }}
    </div>

    <div v-if="uploadedFile" class="mt-4">
      <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
        <div class="flex items-center">
          <svg
            class="w-6 h-6 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <span class="ml-2 text-sm text-gray-600">{{ uploadedFile.name }}</span>
        </div>
        <button
          @click="removeFile"
          class="text-red-600 hover:text-red-800"
        >
          <svg
            class="w-5 h-5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  name: 'FileUpload',
  props: {
    type: {
      type: String,
      required: true,
      validator: (value) => ['avatar', 'deal', 'message'].includes(value)
    },
    accept: {
      type: String,
      default: '*/*'
    }
  },
  emits: ['upload-success', 'upload-error'],
  setup(props, { emit }) {
    const fileInput = ref(null);
    const isDragover = ref(false);
    const error = ref('');
    const uploadedFile = ref(null);

    const validateFile = (file) => {
      if (file.size > 10 * 1024 * 1024) { // 10MB
        error.value = 'Файл слишком большой. Максимальный размер: 10MB';
        return false;
      }
      return true;
    };

    const uploadFile = async (file) => {
      if (!validateFile(file)) return;

      const formData = new FormData();
      formData.append('file', file);
      formData.append('type', props.type);

      try {
        const response = await axios.post('/api/files/upload', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });

        uploadedFile.value = {
          name: file.name,
          path: response.data.path,
          filename: response.data.filename
        };

        emit('upload-success', response.data);
        error.value = '';
      } catch (err) {
        error.value = err.response?.data?.message || 'Ошибка при загрузке файла';
        emit('upload-error', err);
      }
    };

    const handleFileSelect = (event) => {
      const file = event.target.files[0];
      if (file) {
        uploadFile(file);
      }
    };

    const handleDrop = (event) => {
      isDragover.value = false;
      const file = event.dataTransfer.files[0];
      if (file) {
        uploadFile(file);
      }
    };

    const removeFile = () => {
      uploadedFile.value = null;
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    };

    return {
      fileInput,
      isDragover,
      error,
      uploadedFile,
      handleFileSelect,
      handleDrop,
      removeFile
    };
  }
};
</script>

<style scoped>
.file-upload {
  @apply w-full;
}

.upload-area {
  @apply border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer transition-colors duration-200;
}

.upload-area.is-dragover {
  @apply border-primary bg-primary/5;
}

.upload-content {
  @apply flex flex-col items-center justify-center;
}

.hidden {
  @apply hidden;
}
</style> 