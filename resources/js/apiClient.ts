import axios from 'axios';

const apiClient = async (
  url: string,
  method: 'GET' | 'POST' | 'PUT' | 'DELETE' = 'GET',
  data?: Record<string, any>
): Promise<any> => {
  try {
    const isFormData = typeof FormData !== 'undefined' && data instanceof FormData;

    const options =
      method !== 'GET'
        ? {
            method,
            headers: {
              ...(isFormData ? { 'Content-Type': 'multipart/form-data' } : { 'Content-Type': 'application/json; charset=utf-8' })
            },
            data
          }
        : {
            method,
            headers: {
              'Content-Type': 'application/json; charset=utf-8'
            },
            params: data
          };

    const response = await axios(`${window.location.origin}/${url}`, options);
    return response.data ?? response;
  } catch (error: any) {
    // console.error(error.response);
    return {
      error: true,
      msj:
        error?.response?.data?.msj ||
        'Lo sentimos, ocurrió un error.<br>Si el problema persiste contacta a soporte.',
      data: error?.response?.data?.data || 'Error fatal'
    };
  }
};

export default apiClient;