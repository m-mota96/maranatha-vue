import { ElNotification } from 'element-plus';

export const showNotification = (
  message: string,
  title: string = '¡Correcto!',
  type: 'primary' | 'success' | 'warning' | 'info' | 'error' = 'success',
  duration: number = 4500
): void => {
  ElNotification({
    title,
    dangerouslyUseHTMLString: true,
    message,
    type,
    duration
  });
};