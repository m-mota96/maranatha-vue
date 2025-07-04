<script lang="js" setup>
import { onMounted, ref } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import EditUserPermission from './modals/EditUserPermission.vue';

const { module, menu } = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array,
        required: true
    }
});

const users = ref([]);
const editUserPermissionRef = ref(null);

onMounted(() => {
    getUsers();
});

const getUsers = async () => {
    const response = await apiClient('admin/users');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    users.value = response.data;
};

const openModal = (userId, permissions) => {
    editUserPermissionRef.value?.showModal(userId, permissions);
};
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-table :data="users" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
            <el-table-column prop="id" label="#" width="70" align="center" />
            <el-table-column prop="name" label="Nombre" />
            <el-table-column prop="email" label="Correo electrónico" />
            <el-table-column label="Estatus" align="center">
                <template #default="scope">
                    <span class="text-success bold" v-if="scope.row.active == 1">Activo</span>
                    <span class="text-danger bold" v-if="scope.row.active == 0">Inactivo</span>
                </template>
            </el-table-column>
            <el-table-column label="Acciones" width="150" align="center">
                <template #default="scope">
                    <el-button-group>
                        <el-tooltip content="Editar permisos" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal(scope.row.id, scope.row.modules)">
                                <font-awesome-icon :icon="['fas', 'pen']" />
                            </el-button>
                        </el-tooltip>
                    </el-button-group>
                </template>
            </el-table-column>
        </el-table>
    </Layout>
    <EditUserPermission ref="editUserPermissionRef" :get-parent-users="getUsers" />
</template>

<style scoped>

</style>