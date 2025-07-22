<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose, computed } from 'vue';

const { getParentPositions } = defineProps({
    getParentPositions: Function
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const position      = ref({
    id: null,
    name: ''
});
const errors = ref({
    name: false,
});

const showModal = (_position) => {
    resetErrors();
    title.value         = 'Crear nuevo puesto';
    button.value        = 'Guardar';
    position.value.id   = null;
    position.value.name = '';
    if (_position) {
        title.value         = 'Editar puesto';
        button.value        = 'Guardar cambios';
        position.value.id   = _position.id;
        position.value.name = _position.name;
    }
    dialogVisible.value = true;
};

const savePosition = async () => {
    if (validate()) {
        const method   = !position.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/position', method, position.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        getParentPositions();
        showNotification(response.msj);
    }
};


const validate = () => {
    resetErrors();
    let valid = true;
    if (!position.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    return valid;
};

const resetErrors = () => {
    errors.value.name = false;
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="450"
        style="margin-top: 2% !important;"
    >
        <el-col :span="24" class="mb-3">
            <label for="name" class="bold">Puesto <span class="text-danger">*</span></label>
            <el-input v-model="position.name" class="el-form-item" :class="{'is-error': errors.name}" id="name" />
            <span class="text-danger fs-small" v-if="errors.name">El puesto es obligatorio.</span>
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="savePosition">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>