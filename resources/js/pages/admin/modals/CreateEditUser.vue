<script lang="js" setup>
import { ref, defineExpose, onMounted } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const { getParentUsers } = defineProps({
    getParentUsers: Function
});

const dialogVisible = ref(false);
const user = ref({
    id: null,
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});
const errors = ref({
    name: false,
    email: false,
    email_invalid: false,
    password: false,
    password_confirmation: false,
    password_incorrect: false
});
const title   = ref('');
const button  = ref('');

const saveUser = async () => {
    if (validate()) {
        const method   = !user.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/user', method, user.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7000);
            return
        }
        dialogVisible.value = false;
        getParentUsers();
        showNotification(response.msj);
    }
};

const validate = () => {
    resetErrors();
    let valid       = true;
    const mailRegex =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    if (!user.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!user.value.email) {
        errors.value.email = true;
        valid              = false;
    }
    if (user.value.email) {
        if (!mailRegex.test(user.value.email)) {
            errors.value.email_invalid = true;
            valid                      = false;
        }
    }
    if (!user.value.id) {
        if (!user.value.password) {
            errors.value.password = true;
            valid                 = false;
        }
        if (!user.value.password_confirmation) {
            errors.value.password_confirmation = true;
            valid                              = false;
        }
    }
    if (user.value.password && user.value.password_confirmation) {
        if (user.value.password !== user.value.password_confirmation) {
            errors.value.password_incorrect = true;
            valid                           = false;
        }
    }
    return valid;
};

const resetErrors = () => {
    errors.value.name                  = false;
    errors.value.email                 = false;
    errors.value.email_invalid         = false;
    errors.value.password              = false;
    errors.value.password_confirmation = false;
    errors.value.password_incorrect    = false;
};

const showModal = (_user) => {
    resetErrors();
    title.value                      = 'Crear nuevo usuario';
    button.value                     = 'Guardar';
    user.value.id                    = null;
    user.value.name                  = '';
    user.value.email                 = '';
    user.value.password              = '';
    user.value.password_confirmation = '';
    if (_user) {
        title.value      = 'Editar usuario';
        button.value     = 'Guardar cambios';
        user.value.id    = _user.id;
        user.value.name  = _user.name;
        user.value.email = _user.email;
    }
    dialogVisible.value = true;
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="500"
        style="margin-top: 2% !important;"
    >
        <el-col :span="24" class="mb-3">
            <label for="name" class="bold">Nombre <span class="text-danger">*</span></label>
            <el-input v-model="user.name" class="el-form-item mb-0" :class="{'is-error': errors.name}" id="name" />
            <span class="text-danger fs-small" v-if="errors.name">El nombre es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="email" class="bold">Correo electrónico <span class="text-danger">*</span></label>
            <el-input v-model="user.email" class="el-form-item mb-0" :class="{'is-error': errors.email}" id="email" />
            <span class="text-danger fs-small" v-if="errors.email">El correo es obligatorio.</span>
            <span class="text-danger fs-small" v-if="errors.email_invalid">Correo inválido.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="password" class="bold">Contraseña <span class="text-danger" v-if="!user.id">*</span></label>
            <el-input v-model="user.password" class="el-form-item mb-0" :class="{'is-error': errors.password || errors.password_incorrect}" type="password" id="password" />
            <span class="text-danger fs-small" v-if="errors.password">La contraseña es obligatoria.</span>
            <span class="text-danger fs-small" v-if="errors.password_incorrect">Las contraseñas no coinciden.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="password_confirmation" class="bold">Confirmar contraseña <span class="text-danger" v-if="!user.id">*</span></label>
            <el-input v-model="user.password_confirmation" class="el-form-item mb-0" :class="{'is-error': errors.password_confirmation || errors.password_incorrect}" type="password" id="password_confirmation" />
            <span class="text-danger fs-small" v-if="errors.password_confirmation">Confirma la contraseña.</span>
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveUser">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>