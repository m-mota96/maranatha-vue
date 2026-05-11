<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentProviders } = defineProps({
    getParentProviders: {
        type: Function,
        required: false,
        default: null
    }
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const provider      = ref({
    id: null,
    name: '',
    seller: '',
    email: '',
    phone: '',
    address: '',
});
const errors = ref({
    name: false,
    phone_invalid: false,
    email_invalid: false,
});

const showModal = (_provider) => {
    resetErrors();
    title.value                 = 'Crear nuevo proveedor';
    button.value                = 'Guardar';
    provider.value.id           = null;
    provider.value.name         = '';
    provider.value.seller       = '';
    provider.value.email        = '';
    provider.value.phone        = '';
    provider.value.address      = '';
    if (_provider) {
        title.value                 = 'Editar proveedor';
        button.value                = 'Guardar cambios';
        provider.value.id           = _provider.id;
        provider.value.name         = _provider.name;
        provider.value.seller       = _provider.seller;
        provider.value.email        = _provider.email;
        provider.value.phone        = _provider.phone;
        provider.value.address      = _provider.address;
    }
    dialogVisible.value = true;
};

const saveProvider = async () => {
    if (validate()) {
        const method   = !provider.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/provider', method, provider.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        getParentProviders();
        showNotification(response.msj);
    }
};


const validate = () => {
    resetErrors();
    let valid       = true;
    const mailRegex =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    const intRegex  = /^\d{10}$/;
    if (!provider.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (provider.value.phone) {
        if (!intRegex.test(provider.value.phone)) {
            errors.value.phone_invalid = true;
            valid                      = false;
        }
    }
    if (provider.value.email) {
        if (!mailRegex.test(provider.value.email)) {
            errors.value.email_invalid = true;
            valid                      = false;
        }
    }
    return valid;
};

const resetErrors = () => {
    errors.value.name          = false;
    errors.value.phone_invalid = false;
    errors.value.email_invalid = false;
};

const isNumber = (evt) => {
    const charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode < 48 || charCode > 57) {
        evt.preventDefault();
    }
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
            <el-input v-model="provider.name" class="el-form-item" :class="{'is-error': errors.name}" id="name" clearable />
            <span class="text-danger fs-small" v-if="errors.name">El nombre es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="seller" class="bold">Vendedor/Promotor</label>
            <el-input v-model="provider.seller" class="el-form-item" :class="{'is-error': errors.seller}" id="seller" clearable />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="email" class="bold">Correo electrónico</label>
            <el-input v-model="provider.email" class="el-form-item" :class="{'is-error': errors.email_invalid}" id="email" clearable />
            <span class="text-danger fs-small" v-if="errors.email_invalid">Correo inválido.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="phone" class="bold">Teléfono</label>
            <el-input
                v-model="provider.phone"
                class="el-form-item"
                :class="{'is-error': errors.phone || errors.phone_invalid}"
                id="phone"
                @keypress="isNumber($event)"
                :maxlength="10"
                clearable
            />
            <span class="text-danger fs-small" v-if="errors.phone_invalid">Teléfono inválido, debe contener 10 dígitos.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="address" class="bold">Dirección</label>
            <el-input v-model="provider.address" class="el-form-item" id="address" clearable />
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveProvider">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>