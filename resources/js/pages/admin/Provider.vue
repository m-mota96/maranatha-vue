<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditProvider from './modals/CreateEditProvider.vue';

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

const createEditProviderRef = ref(null);
const providers             = ref([]);
const pagination            = ref({
    currentPage: 1,
    pageSize: 30,
    total: 0
});
const search = ref({
    name: '',
    email: '',
    phone: '',
    seller: ''
});
const order = ref({
    orderBy: 'created_at',
    order: 'DESC'
});

onMounted(() => {
    getProviders();
});

const getProviders = async () => {
    const response = await apiClient('admin/providers', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    providers.value = response.data.providers;
    pagination.value.total = response.data.totalRows;
};

const deleteProvider = async (id) => {
    const response = await apiClient(`admin/provider/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    getProviders();
    showNotification(response.msj);
};

const openModal = (data = null) => {
    createEditProviderRef.value?.showModal(data);
};

const resetFilters = () => {
    search.value.name             = '';
    search.value.email            = '';
    search.value.phone            = '';
    search.value.seller           = '';
    order.value.orderBy           = 'created_at';
    order.value.order             = 'DESC';
    getProviders();
}

const handleSizeChange = (val) => {
    getProviders();
}
const handleCurrentChange = (val) => {
    getProviders();
}
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col class="mb-2" :span="4" :offset="15">
            <label for="order">Ordenar por</label>
            <el-select v-model="order.orderBy" @change="getProviders" id="order">
                <el-option :key="0" label="Fecha de creación" value="created_at" />
                <el-option :key="1" label="Proveedor" value="name" />
                <el-option :key="2" label="Vendedor/Promotor" value="seller" />
                <el-option :key="3" label="Correo electrónico" value="email" />
                <el-option :key="3" label="Teléfono" value="phone" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getProviders">
                <el-option :key="0" label="Ascendente" value="ASC" />
                <el-option :key="1" label="Descendente" value="DESC" />
            </el-select>
        </el-col>
        <el-col class="text-center" :span="1">
            <br>
            <el-tooltip
                class="box-item"
                effect="customized"
                content="Limpiar filtros"
                placement="top"
            >
                <font-awesome-icon class="mt-2 pointer" :icon="['fas', 'filter-circle-xmark']" @click="resetFilters" />
            </el-tooltip>
        </el-col>
        <el-col :span="24" class="table-wrapper">
            <el-table :data="providers" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column prop="name">
                    <template #header>
                        <el-input placeholder="Proveedor" title="Escribe para buscar" v-model="search.name" @input="getProviders" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="seller">
                    <template #header>
                        <el-input placeholder="Vendedor/Promotor" title="Escribe para buscar" v-model="search.seller" @input="getProviders" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="email">
                    <template #header>
                        <el-input placeholder="Correo electrónico" title="Escribe para buscar" v-model="search.email" @input="getProviders" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="phone">
                    <template #header>
                        <el-input placeholder="Teléfono" title="Escribe para buscar" v-model="search.phone" @input="getProviders" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="address" label="Dirección" />
                <el-table-column width="150" align="center">
                    <template #header>
                        <el-tooltip content="Nuevo proveedor" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal()">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar proveedor" effect="customized" placement="top">
                                <el-button class="btn-success" @click="openModal(scope.row)">
                                    <font-awesome-icon :icon="['fas', 'pen']" />
                                </el-button>
                            </el-tooltip>
                            <!-- <el-tooltip :content="scope.row.status ? 'Desactivar proveedor' : 'Activar proveedor'" effect="customized" placement="top">
                                <el-button
                                    :class="{'btn-warning': scope.row.status, 'btn-info': !scope.row.status}"
                                    @click="statusCustomer(scope.row)"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </el-button>
                            </el-tooltip> -->
                            <el-popconfirm
                                class="box-item"
                                confirm-button-text="Eliminar"
                                cancel-button-text="Cancelar"
                                :hide-icon="true"
                                confirm-button-type="danger"
                                cancel-button-type="primary"
                                :width="200"
                                title="¿Seguro que deseas eliminar este proveedor?"
                                placement="left"
                                @confirm="deleteProvider(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar proveedor" effect="customized" placement="top">
                                            <el-button class="btn-danger" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
                                                <font-awesome-icon :icon="['fas', 'trash-can']" />
                                            </el-button>
                                        </el-tooltip>
                                    </span>
                                </template>
                            </el-popconfirm>
                        </el-button-group>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                class="mt-3 custom-pager"
                v-model:current-page="pagination.currentPage"
                v-model:page-size="pagination.pageSize"
                :page-sizes="[30, 60, 90, 120, 150]"
                layout="sizes, prev, pager, next"
                :total="pagination.total"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
        </el-col>
    </Layout>
    <CreateEditProvider ref="createEditProviderRef" :get-parent-providers="getProviders" />
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>