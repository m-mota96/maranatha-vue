<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentStaff, positions } = defineProps({
    getParentStaff: Function,
    positions: Array,
    services: Array
});

const title           = ref('');
const button          = ref('');
const dialogVisible   = ref(false);
const appUrl          = ref(window.location.origin);
const token           = ref(document.head.querySelector('meta[name="csrf-token"]').getAttribute('content'));
const upload          = ref(null);
const imagePreviewUrl = ref(null);
const staff           = ref({
    id: null,
    position_id: '',
    name: '',
    first_name: '',
    last_name: '',
    birthdate: '',
    email : '',
    phone: '',
    curp: '',
    rfc: '',
    commission: '',
    image_profile: '',
    password: '',
    password_confirmation: ''
});
const errors = ref({
    position_id: false,
    name: false,
    first_name: false,
    last_name: false,
    email: false,
    password: false,
    password_confirmation: false,
    password_incorrect: false
});

const showModal = (_staff) => {
    resetErrors();
    deletePreview();
    title.value               = 'Crear nuevo staff';
    button.value              = 'Guardar';
    staff.value.id            = null;
    staff.value.position_id   = '';
    staff.value.name          = '';
    staff.value.first_name    = '';
    staff.value.last_name     = '';
    staff.value.birthdate     = '';
    staff.value.email         = '';
    staff.value.phone         = '';
    staff.value.curp          = '';
    staff.value.rfc           = '';
    staff.value.commission    = '';
    staff.value.image_profile = '';
    if (_staff) {
        title.value               = 'Editar staff';
        button.value              = 'Guardar cambios';
        staff.value.id            = _staff.id;
        staff.value.position_id   = _staff.position_id;
        staff.value.name          = _staff.name;
        staff.value.first_name    = _staff.first_name;
        staff.value.last_name     = _staff.last_name;
        staff.value.birthdate     = _staff.birthdate;
        staff.value.email         = _staff.email;
        staff.value.phone         = _staff.phone;
        staff.value.curp          = _staff.curp;
        staff.value.rfc           = _staff.rfc;
        staff.value.commission    = _staff.commission;
        staff.value.image_profile = _staff.image_profile;
    }
    dialogVisible.value = true;
};

const saveStaff = async () => {
    if (!imagePreviewUrl.value) {
        if (validate()) {
            const method   = !staff.value.id ? 'POST' : 'PUT';
            const response = await apiClient('admin/staff', method, {staff: staff.value, image: upload.value});
            if (response.error) {
                showNotification(response.msj, '¡Error!', 'error', 7500);
                return
            }
            dialogVisible.value = false;
            getParentStaff();
            showNotification(response.msj);
        }
    } else {
        if (validate()) {
            upload.value.submit();
        }
    }
};

const validate = () => {
    resetErrors();
    let valid = true;
    if (!staff.value.position_id) {
        errors.value.position_id = true;
        valid                    = false;
    }
    if (!staff.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!staff.value.first_name) {
        errors.value.first_name = true;
        valid                   = false;
    }
    if (!staff.value.last_name) {
        errors.value.last_name = true;
        valid                  = false;
    }
    return valid;
};

const resetErrors = () => {
    errors.value.position_id = false;
    errors.value.name        = false;
    errors.value.first_name  = false;
    errors.value.last_name   = false;
};

const deletePreview = () => {
    if (imagePreviewUrl.value) {
        imagePreviewUrl.value = null;
        upload.value.clearFiles();
    }
};

const handleChange = (uploadFile, uploadFiles) => {
    if (uploadFile.raw) {
        imagePreviewUrl.value = URL.createObjectURL(uploadFile.raw)
    }
}

const handleSuccess = (response, file, fileList) => {
    showNotification('¡Correcto!', response.msj, 'success');
};
const handleError = (response) => {
    upload.value.clearFiles();
    response = JSON.parse(response.message);
    showNotification('¡Error!', response.data, 'error', 10000);
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
        width="1200"
        style="margin-top: 2% !important;"
    >
        <el-row :gutter="20">
            <el-col :span="8" :offset="8" class="mb-4 justify-center items-center" style="display: flex; position: relative;">
                <!-- <font-awesome-icon class="bold text-black pointer" :icon="['fas', 'pen']" style="position: absolute; top: 0; right: 10vh;" /> -->
                 <el-upload
                    ref="upload"
                    class="upload-demo"
                    :data="{
                        id: staff.id,
                        position_id: staff.position_id,
                        name: staff.name,
                        first_name: staff.first_name,
                        last_name: staff.last_name,
                        birthdate: staff.birthdate,
                        email: staff.email,
                        phone: staff.phone,
                        curp: staff.curp,
                        rfc: staff.rfc,
                        commission: staff.commission,
                    }"
                    :action="appUrl+'/admin/staffAndFile'"
                    :headers="{'X-CSRF-TOKEN': token}"
                    multiple
                    :auto-upload="false"
                    accept=".jpg,.png,.jpeg"
                    list-type="picture"
                    :limit="1"
                    style="position: absolute; top: 0; right: 10vh;"
                    :show-file-list="false"
                    :on-change="handleChange"
                    :on-success="handleSuccess"
                    :on-error="handleError"
                >
                    <template #trigger>
                        <font-awesome-icon class="bold text-success pointer" :icon="['fas', 'pen']" v-if="!imagePreviewUrl" style="font-size: 1.2rem;" title="Elegir imagen" />
                    </template>
                    <font-awesome-icon class="bold text-danger pointer" :icon="['fas', 'xmark']" v-if="imagePreviewUrl" style="font-size: 1.2rem;" @click="deletePreview" title="Eliminar imagen" />
                    <!-- <el-button class="ml-3" type="success" @click="submitUpload">
                    upload to server
                    </el-button> -->
                    <template #file="{ file }">
                        <img
                        :src="file.url || URL.createObjectURL(file.raw)"
                        class="preview-image"
                        alt="preview"
                        />
                    </template>
                </el-upload>
                <img class="w-50 rounded-circle" src="/general/user.jpg" alt="Temazcal Maranatha" v-if="!staff.image_profile && !imagePreviewUrl">
                <img class="w-50 rounded-circle" :src="imagePreviewUrl" alt="Temazcal Maranatha" v-if="!staff.image_profile && imagePreviewUrl">
                <img class="w-50 rounded-circle" src="/general/user.jpg" alt="Temazcal Maranatha" v-if="staff.image_profile && !imagePreviewUrl">
            </el-col>
            <el-col :span="8"></el-col>
            <el-col :span="8" class="mb-3">
                <label for="name" class="bold">Nombre <span class="text-danger">*</span></label>
                <el-input v-model="staff.name" class="el-form-item" :class="{'is-error': errors.name}" id="name" />
                <span class="text-danger fs-small" v-if="errors.name">El nombre es obligatorio.</span>
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="first_name" class="bold">Apellido paterno <span class="text-danger">*</span></label>
                <el-input v-model="staff.first_name" class="el-form-item" :class="{'is-error': errors.first_name}" id="first_name" />
                <span class="text-danger fs-small" v-if="errors.first_name">El apellido paterno es obligatorio.</span>
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="last_name" class="bold">Apellido materno <span class="text-danger">*</span></label>
                <el-input v-model="staff.last_name" class="el-form-item" :class="{'is-error': errors.last_name}" id="last_name" />
                <span class="text-danger fs-small" v-if="errors.last_name">El apellido materno es obligatorio.</span>
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="positions" class="bold">Puesto <span class="text-danger">*</span></label>
                <el-select
                    class="el-form-item"
                    :class="{'is-error': errors.position_id}"
                    id="positions"
                    v-model="staff.position_id"
                    placeholder="Elige una opción"
                >
                    <el-option
                        v-for="p in positions"
                        :key="p.id"
                        :value="p.id"
                        :label="p.name"
                    />
                </el-select>
                <span class="text-danger fs-small" v-if="errors.position_id">El puesto es obligatorio.</span>
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="birthdate" class="bold">Fecha de nacimiento</label>
                <el-date-picker
                    class="el-form-item w-100"
                    v-model="staff.birthdate"
                    type="date"
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                    placeholder="Elige la fecha"
                    id="birthdate"
                />
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="email" class="bold">Correo electrónico</label>
                <el-input v-model="staff.email" class="el-form-item" :class="{'is-error': errors.email}" id="email" />
                <span class="text-danger fs-small" v-if="errors.email">El correo es obligatorio.</span>
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="phone" class="bold">Teléfono</label>
                <el-input v-model="staff.phone" class="el-form-item" id="phone" @keypress="isNumber($event)" maxlength="10" />
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="curp" class="bold">Curp</label>
                <el-input v-model="staff.curp" class="el-form-item" id="curp" maxlength="18" @input="staff.curp = staff.curp.toUpperCase()" />
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="rfc" class="bold">Rfc</label>
                <el-input v-model="staff.rfc" class="el-form-item" id="rfc" maxlength="13" @input="staff.rfc = staff.rfc.toUpperCase()" />
            </el-col>
            <el-col :span="8" class="mb-3">
                <label for="commission" class="bold">Comisión</label>
                <el-input v-model="staff.commission" class="el-form-item" id="commission" />
            </el-col>
            <el-col :span="24" class="mb-3">
                <label class="bold mb-2">Servicios que realiza</label><br>
                <div>
                    <el-checkbox v-for="(s, i) in services" :key="i" :label="s.name" size="large" />
                </div>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveStaff">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>