<script lang="js" setup>
import { onMounted, ref } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditUser from './modals/CreateEditUser.vue';

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
const createEditUserRef = ref(null);

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

const openModal = (data = null) => {
    createEditUserRef.value?.showModal(data);
};

const statusUser = async (user) => {
    user.active = user.active === 1 ? 0 : 1;
    const response = await apiClient('admin/user', 'PUT', user);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
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
            <el-table-column align="center">
                <template #header>
                    <el-tooltip content="Nuevo usuario" effect="customized" placement="top">
                        <el-button class="btn-success ps-2 pe-2" @click="openModal()">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                        </el-button>
                    </el-tooltip>
                </template>
                <template #default="scope">
                    <el-button-group>
                        <el-tooltip content="Editar usuario" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal(scope.row)">
                                <font-awesome-icon :icon="['fas', 'pen']" />
                            </el-button>
                        </el-tooltip>
                        <el-tooltip :content="scope.row.active ? 'Desactivar usuario' : 'Activar usuario'" effect="customized" placement="top">
                            <el-button
                                :class="{'btn-danger': scope.row.active, 'btn-info': !scope.row.active}"
                                class="ps-2 pe-2"
                                @click="statusUser(scope.row)"
                            >
                                <font-awesome-icon :icon="['fas', 'eye']" />
                            </el-button>
                        </el-tooltip>
                    </el-button-group>
                </template>
            </el-table-column>
        </el-table>
    </Layout>
    <CreateEditUser ref="createEditUserRef" :get-parent-users="getUsers" />
</template>

<style scoped>

</style>