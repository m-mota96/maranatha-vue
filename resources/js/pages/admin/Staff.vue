<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditStaff from './modals/CreateEditStaff.vue';
import StaffSchedules from './modals/StaffSchedules.vue';

const { module, menu, positions, services } = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array,
        required: true
    },
    positions: {
        type: Array,
        required: true
    },
    services: {
        type: Array,
        required: true
    }
});

const staffSchedulesRef   = ref(null);
const createEditStaffRef = ref(null);
const staff             = ref([]);
const pagination           = ref({
    currentPage: 1,
    pageSize: 10,
    total: 0
});
const search = ref({
    position_id: 0,
    name: '',
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    status: 'all'
});
const order = ref({
    orderBy: 'id',
    order: 'DESC'
});

onMounted(() => {
    getStaff();
});

const getStaff = async () => {
    const response = await apiClient('admin/staff', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    staff.value = response.data.staff;
    pagination.value.total = response.data.totalRows;
};

const statusStaff = async (_staff) => {
    _staff.status = _staff.status === 1 ? 0 : 1;
    const response = await apiClient('admin/staff', 'PUT', {id: _staff.id, status: _staff.status});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
};

const deleteStaff = async (id) => {
    const response = await apiClient(`admin/staff/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    getStaff();
    showNotification(response.msj);
};

const openModal = (data = null) => {
    createEditStaffRef.value?.showModal(data);
};

const openModalSchedule = (id,name, schedule) => {
    staffSchedulesRef.value?.showModal(id, name, schedule);
};

const resetFilters = () => {
    search.value.service_type_id  = 0;
    search.value.name             = '';
    search.value.first_name       = '';
    search.value.last_name        = '';
    search.value.email            = '';
    search.value.phone            = '';
    search.value.status           = 'all';
    order.value.orderBy           = 'id';
    order.value.order             = 'DESC';
    getStaff();
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const handleSizeChange = (val) => {
    getStaff();
}
const handleCurrentChange = (val) => {
    getStaff();
}
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col class="mb-2" :span="4" :offset="15">
            <label for="order">Ordernar por</label>
            <el-select v-model="order.orderBy" @change="getStaff" id="order">
                <el-option :key="0" label="Id" value="id" />
                <el-option :key="1" label="Puesto" value="position_id" />
                <el-option :key="2" label="Nombre" value="name" />
                <el-option :key="2" label="Apellido paterno" value="first_name" />
                <el-option :key="2" label="Apellido materno" value="last_name" />
                <el-option :key="3" label="Correo electrónico" value="email" />
                <el-option :key="4" label="Teléfono" value="phone" />
                <el-option :key="6" label="Estatus" value="status" />
                <el-option :key="6" label="Comisión" value="commission" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getStaff">
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
            <el-table :data="staff" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column prop="position.name">
                    <template #header>
                        <el-select placeholder="Puesto" v-model="search.position_id" @change="getStaff">
                            <el-option :value="0" label="Puesto" />
                            <el-option
                                v-for="p in positions"
                                :key="p.id"
                                :value="p.id"
                                :label="p.name"
                            />
                        </el-select>
                    </template>
                </el-table-column>
                <el-table-column prop="name">
                    <template #header>
                        <el-input placeholder="Nombre" title="Escribe para buscar" v-model="search.name" @input="getStaff" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="first_name">
                    <template #header>
                        <el-input placeholder="Apellido paterno" title="Escribe para buscar" v-model="search.first_name" @input="getStaff" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="last_name">
                    <template #header>
                        <el-input placeholder="Apellido materno" title="Escribe para buscar" v-model="search.last_name" @input="getStaff" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="email">
                    <template #header>
                        <el-input placeholder="Correo electrónico" title="Escribe para buscar" v-model="search.email" @input="getStaff" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="phone">
                    <template #header>
                        <el-input placeholder="Teléfono" title="Escribe para buscar" v-model="search.phone" @input="getStaff" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="curp" label="Curp" />
                <el-table-column prop="rfc" label="Rfc" />
                <el-table-column prop="commission" label="Comisión" />
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-select placeholder="Estatus" v-model="search.status" @change="getStaff">
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
                <el-table-column width="200" align="center">
                    <template #header>
                        <el-tooltip content="Nuevo staff" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal()">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar staff" effect="customized" placement="top">
                                <el-button class="btn-success ps-2 pe-2" @click="openModal(scope.row)">
                                    <font-awesome-icon :icon="['fas', 'pen']" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip content="Editar horarios" effect="customized" placement="top" v-if="scope.row.services.length">
                                <el-button
                                    type="primary"
                                    class="ps-2 pe-2"
                                    @click="openModalSchedule(scope.row.id, `${scope.row.name} ${scope.row.first_name} ${scope.row.last_name}`, scope.row.schedules)"
                                >
                                    <font-awesome-icon :icon="['far', 'clock']" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip :content="scope.row.status ? 'Desactivar staff' : 'Activar staff'" effect="customized" placement="top">
                                <el-button
                                    :class="{'btn-warning': scope.row.status, 'btn-info': !scope.row.status}"
                                    class="ps-2 pe-2"
                                    @click="statusStaff(scope.row)"
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
                                title="¿Seguro que deseas eliminar este staff?"
                                placement="left"
                                @confirm="deleteStaff(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar staff" effect="customized" placement="top">
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
    <CreateEditStaff ref="createEditStaffRef" :get-parent-staff="getStaff" :positions="positions" :services="services" />
    <StaffSchedules ref="staffSchedulesRef" :get-parent-satff="getStaff"/>
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>