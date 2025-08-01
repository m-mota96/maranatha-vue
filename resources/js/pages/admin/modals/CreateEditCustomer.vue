<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentCustomers } = defineProps({
    getParentCustomers: {
        type: Function,
        required: false,
        default: null
    }
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const customer      = ref({
    id: null,
    name: '',
    birthdate: '',
    email: '',
    phone: '',
    company_name: '',
    rfc: '',
    address: '',
});
const errors = ref({
    name: false,
    phone: false,
    phone_invalid: false,
    email_invalid: false,
});

const showModal = (_customer) => {
    resetErrors();
    title.value                 = 'Crear nuevo cliente';
    button.value                = 'Guardar';
    customer.value.id           = null;
    customer.value.name         = '';
    customer.value.birthdate    = '';
    customer.value.email        = '';
    customer.value.phone        = '';
    customer.value.company_name = '';
    customer.value.rfc          = '';
    customer.value.address      = '';
    if (_customer) {
        title.value                 = 'Editar cliente';
        button.value                = 'Guardar cambios';
        customer.value.id           = _customer.id;
        customer.value.name         = _customer.name;
        customer.value.birthdate    = _customer.birthdate;
        customer.value.email        = _customer.email;
        customer.value.phone        = _customer.phone;
        customer.value.company_name = _customer.company_name;
        customer.value.rfc          = _customer.rfc;
        customer.value.address      = _customer.address;
    }
    dialogVisible.value = true;
};

const saveCustomer = async () => {
    if (validate()) {
        const method   = !customer.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/customer', method, customer.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        if (getParentCustomers) {
            getParentCustomers();
        }
        showNotification(response.msj);
    }
};


const validate = () => {
    resetErrors();
    let valid       = true;
    const mailRegex =  /^\w+([.-_+]?\w+)*@\w+([.-]?\w+)*(\.\w{2,10})+$/;
    const intRegex  = /^\d{10}$/;
    if (!customer.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!customer.value.phone) {
        errors.value.phone = true;
        valid             = false;
    } else {
        if (!intRegex.test(customer.value.phone)) {
            errors.value.phone_invalid = true;
            valid                      = false;
        }
    }
    if (customer.value.email) {
        if (!mailRegex.test(customer.value.email)) {
            errors.value.email_invalid = true;
            valid                      = false;
        }
    }
    return valid;
};

const resetErrors = () => {
    errors.value.name          = false;
    errors.value.phone         = false;
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
            <label for="name" class="bold">Nombre completo <span class="text-danger">*</span></label>
            <el-input v-model="customer.name" class="el-form-item" :class="{'is-error': errors.name}" id="name" clearable />
            <span class="text-danger fs-small" v-if="errors.name">El nombre es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="birthdate" class="bold">Fecha de nacimiento</label>
            <el-date-picker
                class="el-form-item w-100"
                v-model="customer.birthdate"
                type="date"
                format="DD/MM/YYYY"
                value-format="YYYY-MM-DD"
                placeholder="Elige la fecha"
                id="birthdate"
            />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="email" class="bold">Correo electrónico</label>
            <el-input v-model="customer.email" class="el-form-item" :class="{'is-error': errors.email_invalid}" id="email" clearable />
            <span class="text-danger fs-small" v-if="errors.email_invalid">Correo inválido.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="phone" class="bold">Teléfono <span class="text-danger">*</span></label>
            <el-input
                v-model="customer.phone"
                class="el-form-item"
                :class="{'is-error': errors.phone || errors.phone_invalid}"
                id="phone"
                @keypress="isNumber($event)"
                :maxlength="10"
                clearable
            />
            <span class="text-danger fs-small" v-if="errors.phone">El teléfono es obligatorio.</span>
            <span class="text-danger fs-small" v-if="errors.phone_invalid">Teléfono inválido, debe contener 10 dígitos.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="company_name" class="bold">Razón social</label>
            <el-input v-model="customer.company_name" class="el-form-item" id="company_name" clearable />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="rfc" class="bold">Rfc</label>
            <el-input v-model="customer.rfc" class="el-form-item" id="rfc" :maxlength="13" @input="customer.rfc = customer.rfc.toUpperCase()" clearable />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="address" class="bold">Dirección</label>
            <el-input v-model="customer.address" class="el-form-item" id="address" clearable />
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveCustomer">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>