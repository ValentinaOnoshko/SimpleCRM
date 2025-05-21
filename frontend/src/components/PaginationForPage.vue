<template>
  <div class="pagination">
    <button
      :disabled="pagination.current_page === 1"
      @click="changePage(pagination.current_page - 1)"
    >
      Назад
    </button>
    <span>{{ pagination.current_page }} / {{ pagination.last_page }}</span>
    <button
      :disabled="pagination.current_page === pagination.last_page"
      @click="changePage(pagination.current_page + 1)"
    >
      Вперёд
    </button>
  </div>
</template>

<script>
export default {
  props: {
    pagination: Object,
  },
  methods: {
    changePage(page) {
      this.$emit('page-changed', page);
    }
  }
};
</script>

<style scoped>
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}
button {
  padding: 5px 10px;
  margin: 0 5px;
}
</style>
