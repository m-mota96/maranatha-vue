import { ElNotification } from 'element-plus';

export const showNotification = (title, message, type, duration = 4500) => {
    ElNotification({
        title,
        dangerouslyUseHTMLString: true,
        message,
        type,
        duration
    });
};