<script lang="js" setup>
import { ref, defineExpose } from 'vue';
import { showNotification } from '@/notification';

const { closeParentModal, url, validateParent, getParentAppointments } = defineProps({
    closeParentModal: {
        type: Function,
        required: false
    },
    url: {
        type: String,
        required: true
    },
    validateParent: {
        type: Function,
        required: false
    },
    getParentAppointments: {
        type: Function,
        required: false
    }
});

const loading            = defineModel();
const uploadRef          = ref(null);
const token              = ref(document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'));
const appUrl             = ref(window.location.origin);
const selectedFiles      = ref([]);
const data               = ref({});

const uploadInfo = async (_data) => {
    loading.value = false;
    data.value    = _data;
    if (!selectedFiles.value.length) {
        showNotification('Selecciona un archivo', '¡Error!', 'error');
        return
    }
    if (validateParent()) {
        loading.value = true;
        uploadRef.value?.submit();
    }
};

const handleSuccess = (response, file, fileList) => {
    getParentAppointments();
    loading.value = false;
    showNotification(response.msj);
    closeParentModal();
    clearFile();
};

const handleError = (response) => {
    loading.value = false;
    response = JSON.parse(response.message);
    showNotification(response.data, '¡Error!', 'error', 10000);
};

const handleFileChange = (file, fileList) => {
    selectedFiles.value = fileList;
};

const clearFile = () => {
    if (uploadRef.value) {
        uploadRef.value?.clearFiles();
    }
};

defineExpose({
    uploadInfo
});
</script>

<template>
    <el-upload
        ref="uploadRef"
        class="upload-demo mb-0 mt-1"
        drag
        :data="data"
        :action="appUrl+'/'+url"
        :headers="{'X-CSRF-TOKEN': token}"
        multiple
        :auto-upload="false"
        accept=".jpg,.png"
        list-type="picture"
        :limit="1"
        :on-success="handleSuccess"
        :on-error="handleError"
        :on-change="handleFileChange"
    >
        <el-icon class="el-icon--upload"><font-awesome-icon :icon="['fas', 'cloud-arrow-up']" /></el-icon>
        <div class="el-upload__text">
            Arrastra la imagen o haz <em>clic para cargar</em>
        </div>
        <template #tip>
            <div class="el-upload__tip">
                <!-- jpg/png files with a size less than 500kb -->
                <span class="text-sm has-text-dark mt-4"><b>NOTA:</b> La imagen debe ser en formato jpg o png no mayor a 1MB.</span>
            </div>
        </template>
    </el-upload>
</template>