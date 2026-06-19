export const formatRuDate = (value: string): string => {
  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    timeZone: 'Europe/Moscow',
    year: 'numeric',
  }).format(new Date(value));
};

export const formatRuDateTime = (value: string): string => {
  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    timeZone: 'Europe/Moscow',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
};

export const formatRuNumber = (value: number): string => {
  return new Intl.NumberFormat('ru-RU').format(value);
};
