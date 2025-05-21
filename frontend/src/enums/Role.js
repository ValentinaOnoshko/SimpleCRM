const Role = {
  CLIENT: {
    value: 'client',
    label: 'Клиент'
  },
  PERFORMER: {
    value: 'performer',
    label: 'Исполнитель'
  }
};

Object.defineProperty(Role, 'values', {
  value: Object.values(Role).map(role => role.value),
  configurable: false,
  writable: false,
  enumerable: false
});

export { Role as RoleEnum };
