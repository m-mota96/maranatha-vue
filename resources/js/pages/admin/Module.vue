<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditMenu from './modals/CreateEditMenu.vue';

const { menu, module } = defineProps({
    menu: {
        type: Array,
        required: true
    },
    module: {
        type: Object,
        required: true
    }
});

const modules = ref([]);
const createEditMenuRef = ref(null);

onMounted(() => {
    getModules();
});

const getModules = async () => {
    const response = await apiClient('admin/modules');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    modules.value = response.data;
};

const openModal = (data = null) => {
    createEditMenuRef.value?.showModal(data);
};

const statusModule = async (module) => {
    module.status = module.status === 1 ? 0 : 1;
    const response = await apiClient('admin/module', 'PUT', module);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
};
</script>

<template>
    <Layout :menu="menu" :module="module" :dad="module.dad.name">
        <el-table :data="modules" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
            <el-table-column prop="id" label="#" width="70" align="center" />
            <el-table-column label="Padre">
                <template #default="scope">
                    <span>{{scope.row.dad ? scope.row.dad.name : ''}}</span>
                </template>
            </el-table-column>
            <el-table-column prop="name" label="Nombre" />
            <el-table-column prop="icon" label="Icono" />
            <el-table-column prop="target" label="Url" width="270" />
            <el-table-column prop="class" label="Clase" />
            <el-table-column prop="description" label="Descripción" />
            <el-table-column label="Estatus" align="center">
                <template #default="scope">
                    <span class="text-success bold" v-if="scope.row.status == 1">Activo</span>
                    <span class="text-danger bold" v-if="scope.row.status == 0">Inactivo</span>
                </template>
            </el-table-column>
            <el-table-column align="center">
                <template #header>
                    <el-tooltip content="Nuevo módulo" effect="customized" placement="top">
                        <el-button class="btn-success ps-2 pe-2" @click="openModal()">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                        </el-button>
                    </el-tooltip>
                </template>
                <template #default="scope">
                    <el-button-group>
                        <el-tooltip content="Editar módulo" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal(scope.row)">
                                <font-awesome-icon :icon="['fas', 'pen']" />
                            </el-button>
                        </el-tooltip>
                        <el-tooltip :content="scope.row.status ? 'Desactivar módulo' : 'Activar módulo'" effect="customized" placement="top">
                            <el-button
                                :class="{'btn-danger': scope.row.status, 'btn-info': !scope.row.status}"
                                class="ps-2 pe-2"
                                @click="statusModule(scope.row)"
                            >
                                <font-awesome-icon :icon="['fas', 'eye']" />
                            </el-button>
                        </el-tooltip>
                    </el-button-group>
                </template>
            </el-table-column>
        </el-table>
    </Layout>
    <CreateEditMenu ref="createEditMenuRef" :get-parent-modules="getModules" />
</template>

<style scoped>

</style>