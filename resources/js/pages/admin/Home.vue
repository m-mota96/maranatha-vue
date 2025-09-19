<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import CreateEditCustomer from './modals/CreateEditCustomer.vue';
import CreateAppointment from './modals/CreateAppointment.vue';
import CreateSale from './modals/CreateSale.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const { menu, serviceType } = defineProps({
    menu: {
        type: Array,
        required: true
    },
    serviceType: {
        type: Array,
        required: true
    }
});

const createEditCustomerRef = ref(null);
const createAppoimentRef    = ref(null);
const createSaleRef         = ref(null);
const appointments          = ref([]);
const search                = ref({
    currentDate: '',
});
const pagination            = ref({
    currentPage: 1,
    pageSize: 10,
    total: 0
});
const order = ref({
    orderBy: 'id',
    order: 'DESC'
});

onMounted(() => {
    search.value.currentDate = formatDate(new Date());
    getAppointments();
});

const getAppointments = async () => {
    const response = await apiClient('admin/appointments', 'GET', { pagination: pagination.value, search: search.value, order: order.value });
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    appointments.value     = response.data.appointments;
    pagination.value.total = response.data.totalRows;
};

const formatDate = (date) => {
    if (!date) return ''
    return date.toISOString().split('T')[0]
};

const setClass = (_status) => {
    switch (_status) {
        case 'Cancelada':
        case 'Eliminada':
            return 'text-danger'
        default:
            return 'text-warning';
    }
};

const time = (time)=> {
    let hour = parseInt(time.substring(0, 2));
    const txt = (hour >= 12) ? 'PM' : 'AM';
    hour = (hour > 12) ? (hour - 12) : hour;
    hour = (hour < 10) ? '0'+hour : hour;
    return hour+time.substring(2, 5)+' '+txt;
};

const newCustomer = (data = null) => {
    createEditCustomerRef.value?.showModal(data);
};

const newAppoiment = () => {
    createAppoimentRef.value?.showModal();
};

const newSale = (appointmentId) => {
    createSaleRef.value?.showModal(appointmentId);
};

const handleSizeChange = (val) => {
    getAppointments();
}
const handleCurrentChange = (val) => {
    getAppointments();
}
</script>

<template>
    <Layout :menu="menu" :background="false">
        <el-col :span="24">
            <el-row :gutter="20">
                <el-col :span="18">
                    <el-card class="pt-6 pb-6">
                        <el-row>
                            <el-col :span="24" class="text-right mb-4">
                                <el-date-picker
                                    v-model="search.currentDate"
                                    type="date"
                                    format="DD/MM/YYYY"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Elige la fecha"
                                    :clearable="false"
                                    @change="getAppointments"
                                />
                            </el-col>
                            <el-col :soan="24">
                                <table class="table table-striped fs-small">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th>Cliente</th>
                                            <th class="text-center">Hora de inicio</th>
                                            <th>Servicios</th>
                                            <th class="text-center">Estatus</th>
                                            <th>Agendada por</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!appointments.length">
                                            <td class="text-center" colspan="7">
                                                Ningún dato disponible en esta tabla
                                            </td> 
                                        </tr>
                                        <tr v-for="a in appointments">
                                            <td class="text-center" width="5%">{{ a.id }}</td>
                                            <td width="15%">{{ a.customer.name }}</td>
                                            <td class="text-center" width="10%">{{ time(a.horary) }}</td>
                                            <td>
                                                <span class="badge text-bg-primary mr-2 mb-1" v-for="s in a.services">{{ s.name }}</span>
                                            </td>
                                            <td class="text-center bold" :class="setClass(a.status.name)" width="10%">{{ a.status.name }}</td>
                                            <td>{{ a.created_by.name }}</td>
                                            <td class="text-center">
                                                <el-button-group>
                                                    <el-tooltip content="Realizar venta" effect="customized" placement="top">
                                                        <el-button
                                                            type="success"
                                                            class="ps-2 pe-2"
                                                            @click="newSale(a.id)"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'dollar-sign']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-tooltip content="Eitar cita" effect="customized" placement="top">
                                                        <el-button
                                                            type="primary"
                                                            class="ps-2 pe-2"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'pen']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-tooltip content="Cancelar cita" effect="customized" placement="top">
                                                        <el-button
                                                            type="warning"
                                                            class="ps-2 pe-2"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'times']" />
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
                                                        title="¿Seguro que deseas eliminar esta cita?"
                                                        placement="left"
                                                    >
                                                        <template #reference>
                                                            <span>
                                                                <el-tooltip content="Eliminar cita" effect="customized" placement="top">
                                                                    <el-button type="danger" class="ps-2 pe-2" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
                                                                        <font-awesome-icon :icon="['fas', 'trash-can']" />
                                                                    </el-button>
                                                                </el-tooltip>
                                                            </span>
                                                        </template>
                                                    </el-popconfirm>
                                                </el-button-group>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
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
                        </el-row>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="p-6">
                        <el-row :gutter="20">
                            <el-col :span="12" class="mb-3">
                                <div class="card-warning p-4" @click="newAppoiment()">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'clipboard']" /></h1>
                                    <h6 class="text-center">Nueva cita</h6>
                                </div>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div  class="card-danger p-4">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'dollar-sign']" /></h1>
                                    <h6 class="text-center">Nueva venta</h6>
                                </div>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div  class="card-info p-4" @click="newCustomer()">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'user-plus']" /></h1>
                                    <h6 class="text-center">Nuevo miembro</h6>
                                </div>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </Layout>
    <CreateEditCustomer ref="createEditCustomerRef" />
    <CreateAppointment ref="createAppoimentRef" :service-type="serviceType" />
    <CreateSale ref="createSaleRef" />
</template>

<style scoped>
.card-warning {
    color: #FFA800;
    background-color: #FFF4DE;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-warning:hover {
    background-color: #FFA800;
    color: white;
}
.card-danger {
    color: #F64E60;
    background-color: #FFE2E5;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-danger:hover {
    background-color: #F64E60;
    color: white;
}
.card-info {
    color: #8950FC;
    background-color: #EEE5FF;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-info:hover {
    background-color: #8950FC;
    color: white;
}
</style>