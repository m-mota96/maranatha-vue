<script lang="js" setup>
import { ref, defineExpose, onMounted } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const { getParentModules } = defineProps({
    getParentModules: Function
});

const dialogVisible = ref(false);
const menu = ref({
    id: null,
    menu_id: null,
    name: '',
    icon: '',
    target: '',
    class: '',
    description: '',
    status: null
});
const errors = ref({
    name: false,
    target: false
});
const title   = ref('');
const button  = ref('');
const modules = ref([]);

onMounted(() => {
    getModules();
});

const getModules = async () => {
    const response = await apiClient('admin/parentModules');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    modules.value = response.data;
};

const saveModule = async () => {
    if (validate()) {
        const method   = !menu.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/module', method, menu.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7000);
            return
        }
        dialogVisible.value = false;
        getModules();
        getParentModules();
        showNotification(response.msj);
    }
};

const validate = () => {
    resetErrors();
    let valid = true;
    if (!menu.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    return valid;
};

const resetErrors = () => {
    errors.value.name   = false;
};

const showModal = (_menu) => {
    resetErrors();
    title.value            = 'Crear nuevo módulo';
    button.value           = 'Guardar';
    menu.value.id          = null;
    menu.value.menu_id     = 0;
    menu.value.name        = '';
    menu.value.icon        = '';
    menu.value.target      = '';
    menu.value.class       = '';
    menu.value.description = '';
    if (_menu) {
        title.value            = 'Editar módulo';
        button.value           = 'Guardar cambios';
        menu.value.id          = _menu.id;
        menu.value.menu_id     = !_menu.menu_id ? 0 : _menu.menu_id;
        menu.value.name        = _menu.name;
        menu.value.icon        = _menu.icon;
        menu.value.target      = _menu.target;
        menu.value.class       = _menu.class;
        menu.value.description = _menu.description;
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
            <label for="name" class="bold">Módulo padre</label>
            <el-select v-model="menu.menu_id" placeholder="Elige una opción">
                <el-option :value="0" label="Nuevo Módulo" />
                <el-option
                    v-for="item in modules"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                />
            </el-select>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="name" class="bold">Nombre del módulo <span class="text-danger">*</span></label>
            <el-input v-model="menu.name" class="el-form-item mb-0" :class="{'is-error': errors.name}" id="name" />
            <span class="text-danger fs-small" v-if="errors.name">El nombre del módulo es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="icon" class="bold">Icono</label>
            <el-input v-model="menu.icon" class="el-form-item" id="icon" />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="target" class="bold">Url</label>
            <el-input v-model="menu.target" class="el-form-item" id="target" />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="class" class="bold">Clase</label>
            <el-input v-model="menu.class" class="el-form-item" id="class" />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="description" class="bold">Descripción</label>
            <el-mention
            v-model="menu.description"
            type="textarea"
            class="el-form-item"
            id="description"
            />
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveModule">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>