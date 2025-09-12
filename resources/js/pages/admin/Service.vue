<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditService from './modals/CreateEditService.vue';

const { module, menu, serviceType } = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array,
        required: true
    },
    serviceType: {
        type: Array,
        required: true
    }
});

const createEditServiceRef = ref(null);
const services             = ref([]);
const pagination           = ref({
    currentPage: 1,
    pageSize: 10,
    total: 0
});
const search = ref({
    service_type_id: 0,
    name: '',
    price: '',
    discounted_price: '',
    status: 'all'
});
const order = ref({
    orderBy: 'id',
    order: 'DESC'
});

onMounted(() => {
    getServices();
});

const getServices = async () => {
    const response = await apiClient('admin/services', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    services.value = response.data.services;
    pagination.value.total = response.data.totalRows;
};

const statusService = async (_service) => {
    _service.status = _service.status === 1 ? 0 : 1;
    const response = await apiClient('admin/service', 'PUT', _service);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
};

const deleteService = async (id) => {
    const response = await apiClient(`admin/service/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    getServices();
    showNotification(response.msj);
};

const openModal = (data = null) => {
    createEditServiceRef.value?.showModal(data);
};

const resetFilters = () => {
    search.value.service_type_id  = 0;
    search.value.name             = '';
    search.value.price            = '';
    search.value.discounted_price = '';
    search.value.status           = 'all';
    order.value.orderBy           = 'id';
    order.value.order             = 'DESC';
    getServices();
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const handleSizeChange = (val) => {
    getServices();
}
const handleCurrentChange = (val) => {
    getServices();
}
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col class="mb-2" :span="4" :offset="15">
            <label for="order">Ordenar por</label>
            <el-select v-model="order.orderBy" @change="getServices" id="order">
                <el-option :key="0" label="Id" value="id" />
                <el-option :key="1" label="Tipo de servicio" value="service_type_id" />
                <el-option :key="2" label="Servicio" value="name" />
                <el-option :key="3" label="Precio" value="price" />
                <el-option :key="4" label="Precio especial" value="discounted_price" />
                <el-option :key="5" label="Duración del servicio" value="time" />
                <el-option :key="6" label="Estatus" value="status" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getServices">
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
            <el-table :data="services" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column prop="service_type.name">
                    <template #header>
                        <el-select placeholder="Tipo de servicio" v-model="search.service_type_id" @change="getServices">
                            <el-option :value="0" label="Tipo de servicio" />
                            <el-option
                                v-for="s in serviceType"
                                :key="s.id"
                                :value="s.id"
                                :label="s.name"
                            />
                        </el-select>
                    </template>
                </el-table-column>
                <el-table-column prop="name">
                    <template #header>
                        <el-input placeholder="Servicio" title="Escribe para buscar" v-model="search.name" @input="getServices" clearable />
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-input placeholder="Precio" title="Escribe para buscar" v-model="search.price" @input="getServices" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.price) }}
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-input placeholder="Precio especial" title="Escribe para buscar" v-model="search.discounted_price" @input="getServices" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.discounted_price) }}
                    </template>
                </el-table-column>
                <el-table-column label="Duración del servicio">
                    <template #default="scope">
                        <span v-if="(scope.row.time % 60) !== 0">{{ scope.row.time }} minutos</span>
                        <span v-if="(scope.row.time % 60) === 0">{{ scope.row.time / 60 }} {{ (scope.row.time / 60) === 1 ? 'hora' : 'horas' }}</span>
                    </template>
                </el-table-column>
                <el-table-column label="Color" align="center">
                    <template #default="scope">
                        <font-awesome-icon v-if="scope.row.color" :icon="['fas', 'circle']" :style="{color: scope.row.color}" />
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-select placeholder="Estatus" v-model="search.status" @change="getServices">
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
                        <el-tooltip content="Nuevo servicio" effect="customized" placement="top">
                            <el-button class="btn-success ps-2 pe-2" @click="openModal()">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar servicio" effect="customized" placement="top">
                                <el-button class="btn-success ps-2 pe-2" @click="openModal(scope.row)">
                                    <font-awesome-icon :icon="['fas', 'pen']" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip :content="scope.row.status ? 'Desactivar servicio' : 'Activar servicio'" effect="customized" placement="top">
                                <el-button
                                    :class="{'btn-warning': scope.row.status, 'btn-info': !scope.row.status}"
                                    class="ps-2 pe-2"
                                    @click="statusService(scope.row)"
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
                                title="¿Seguro que deseas eliminar este servicio?"
                                placement="left"
                                @confirm="deleteService(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar servicio" effect="customized" placement="top">
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
    <CreateEditService ref="createEditServiceRef" :get-parent-services="getServices" :serviceType="serviceType" />
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>