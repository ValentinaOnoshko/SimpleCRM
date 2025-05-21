export const DealStatus = {
  NEW: {
    value: 'new',
    label: 'Новая',
    color: 'yellow'
  },
  IN_PROGRESS: {
    value: 'in_progress',
    label: 'В процессе',
    color: 'blue'
  },
  COMPLETED: {
    value: 'completed',
    label: 'Завершенная',
    color: 'green'
  },
  CANCELED: {
    value: 'canceled',
    label: 'Отмененная',
    color: 'red'
  }
};

Object.defineProperty(DealStatus, 'values', {
  value: Object.values(DealStatus).map(status => status.value),
  configurable: false,
  writable: false,
  enumerable: false
});

Object.defineProperty(DealStatus, 'labels', {
  value: Object.values(DealStatus).map(status => status.label),
  configurable: false,
  writable: false,
  enumerable: false
});
