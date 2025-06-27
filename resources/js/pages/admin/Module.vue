<script setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';

const props = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array
    }
});

const modules = ref([]);

onMounted(() => {
    getModules();
});

const getModules = async () => {
    const response = await apiClient('admin/modules', 'GET', null);
    if (response.error) {

    }
    modules.value = response.data;
};
</script>

<template>
    <Layout :menu="props.menu" :module="props.module" :dad="props.module.dad.name">
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
            <!-- <el-table-column align="center">
                <template #header>
                    <el-tooltip content="Nuevo módulo" effect="customized" placement="top">
                        <el-button class="btn-success ps-2 pe-2">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                        </el-button>
                    </el-tooltip>
                </template>
                <template #default="scope">
                    <el-button-group>
                        <el-tooltip content="Editar módulo" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" :icon="Edit">
                                <font-awesome-icon :icon="['fas', 'pen']" />
                            </el-button>
                        </el-tooltip>
                        <el-tooltip content="Desactivar módulo" effect="customized" placement="top">
                            <el-button class="btn-danger ps-2 pe-2" :icon="Share">
                                <font-awesome-icon :icon="['fas', 'eye']" />
                            </el-button>
                        </el-tooltip>
                    </el-button-group>
                </template>
            </el-table-column> -->
        </el-table>
    </Layout>
</template>

<style scoped>

</style>