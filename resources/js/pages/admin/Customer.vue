<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditCustomer from './modals/CreateEditCustomer.vue';
import { dateEs } from '@/dateEs';

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

const createEditCustomerRef = ref(null);
const customers             = ref([]);
const pagination            = ref({
    currentPage: 1,
    pageSize: 10,
    total: 0
});
const search = ref({
    name: '',
    email: '',
    phone: '',
    rfc: '',
    status: 'all'
});
const order = ref({
    orderBy: 'id',
    order: 'DESC'
});

onMounted(() => {
    getCustomers();
});

const getCustomers = async () => {
    const response = await apiClient('admin/customers', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    customers.value = response.data.customers;
    pagination.value.total = response.data.totalRows;
};

const statusCustomer = async (_customer) => {
    _customer.status = _customer.status === 1 ? 0 : 1;
    const response   = await apiClient('admin/customer', 'PUT', _customer);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
};

const deleteCustomer = async (id) => {
    const response = await apiClient(`admin/customer/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    getCustomers();
    showNotification(response.msj);
};

const openModal = (data = null) => {
    createEditCustomerRef.value?.showModal(data);
};

const resetFilters = () => {
    search.value.name             = '';
    search.value.email            = '';
    search.value.phone            = '';
    search.value.rfc              = '';
    search.value.status           = 'all';
    order.value.orderBy           = 'id';
    order.value.order             = 'DESC';
    getCustomers();
}

const handleSizeChange = (val) => {
    getCustomers();
}
const handleCurrentChange = (val) => {
    getCustomers();
}
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col class="mb-2" :span="4" :offset="15">
            <label for="order">Ordernar por</label>
            <el-select v-model="order.orderBy" @change="getCustomers" id="order">
                <el-option :key="0" label="Id" value="id" />
                <el-option :key="1" label="Nombre" value="name" />
                <el-option :key="2" label="Correo electrónico" value="email" />
                <el-option :key="3" label="Teléfono" value="phone" />
                <el-option :key="4" label="Razón social" value="company_name" />
                <el-option :key="5" label="Rfc" value="rfc" />
                <el-option :key="6" label="Dirección" value="address" />
                <el-option :key="7" label="Estatus" value="status" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getCustomers">
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
            <el-table :data="customers" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column prop="name">
                    <template #header>
                        <el-input placeholder="Nombre" title="Escribe para buscar" v-model="search.name" @input="getCustomers" clearable />
                    </template>
                </el-table-column>
                <el-table-column label="Fecha de nacimiento">
                    <template #default="scope">
                        {{ scope.row.birthdate ? dateEs(scope.row.birthdate, '/', 1) : '' }}
                    </template>
                </el-table-column>
                <el-table-column prop="email">
                    <template #header>
                        <el-input placeholder="Correo electrónico" title="Escribe para buscar" v-model="search.email" @input="getCustomers" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="phone">
                    <template #header>
                        <el-input placeholder="Teléfono" title="Escribe para buscar" v-model="search.phone" @input="getCustomers" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="company_name" label="Razón social"/>
                <el-table-column prop="rfc">
                    <template #header>
                        <el-input placeholder="Rfc" title="Escribe para buscar" v-model="search.rfc" @input="getCustomers" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="address" label="Dirección"/>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-select placeholder="Estatus" v-model="search.status" @change="getCustomers">
                            <el-option value="all" label="Estatus" />
                            <el-option :value="1" label="Activo" />
                            <el-option :value="0" label="Inactivo" />
                        </el-select>
                    </template>
                    <template #default="scope">
                        <span class="text-success bold" v-if="scope.row.status == 1">Activo</span>
                        <span class="text-danger bold" v-if="scope.row.status == 0">Inactivo</span>
                    </template>
                </el-table-column>
                <el-table-column width="150" align="center">
                    <template #header>
                        <el-tooltip content="Nuevo cliente" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal()">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar cliente" effect="customized" placement="top">
                                <el-button class="btn-success ps-2 pe-2" @click="openModal(scope.row)">
                                    <font-awesome-icon :icon="['fas', 'pen']" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip :content="scope.row.status ? 'Desactivar cliente' : 'Activar cliente'" effect="customized" placement="top">
                                <el-button
                                    :class="{'btn-warning': scope.row.status, 'btn-info': !scope.row.status}"
                                    class="ps-2 pe-2"
                                    @click="statusCustomer(scope.row)"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </el-button>
                            </el-tooltip>
                            <el-popconfirm
                                class="box-item"
                                confirm-button-text="Eliminar"
                                cancel-button-text="Cancelar"
                                :hide-icon="true"
                                confirm-button-type="danger"
                                cancel-button-type="primary"
                                :width="200"
                                title="¿Seguro que deseas eliminar este cliente?"
                                placement="left"
                                @confirm="deleteCustomer(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar cliente" effect="customized" placement="top">
                                            <el-button class="btn-danger ps-2 pe-2" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
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
                class="mt-3"
                v-model:current-page="pagination.currentPage"
                v-model:page-size="pagination.pageSize"
                :page-sizes="[10, 20, 30, 40, 50]"
                layout="sizes, prev, pager, next"
                :total="pagination.total"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
        </el-col>
    </Layout>
    <CreateEditCustomer ref="createEditCustomerRef" :get-parent-customers="getCustomers" />
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>