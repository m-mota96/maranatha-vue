<script lang="js" setup>
import { ref, onMounted, onBeforeMount, nextTick } from 'vue';
import Layout from './Layout.vue';
import CreateEditCustomer from './modals/CreateEditCustomer.vue';
import CreateAppointment from './modals/CreateAppointment.vue';
import CreateSale from './modals/CreateSale.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import Swal from 'sweetalert2';
import { dateEs } from '@/dateEs';

const { menu, status, paymentMethods } = defineProps({
    menu: {
        type: Array,
        required: true
    },
    status: {
        type: Array,
        required: true
    },
    paymentMethods: {
        type: Array,
        required: true
    }
});

const createEditCustomerRef = ref(null);
const createAppoimentRef    = ref(null);
const createSaleRef         = ref(null);
const serviceType           = ref([]);
const appointments          = ref([]);
const pendingId             = ref(null);
const search                = ref({
    currentDate: '',
    customer: '',
    horary: '',
    status: '',
    user: '',
});
const pagination            = ref({
    currentPage: 1,
    pageSize: 20,
    total: 0
});
const order = ref({
    orderBy: 'created_at',
    order: 'DESC'
});
const scheduled = ref(0);
const confirmed = ref(0);
const canceled  = ref(0);

onBeforeMount(() => {
    getServices();
});

onMounted(() => {
    search.value.currentDate = formatDate(new Date());
    getAppointments();
});

const getServices = async () => {
    const response = await apiClient('admin/serviceTypes');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    serviceType.value = response.data;
};

const getAppointments = async () => {
    if (!search.value.currentDate) {
        search.value.currentDate = formatDate(new Date());
    }
    const response = await apiClient('admin/appointments', 'GET', { pagination: pagination.value, search: search.value, order: order.value });
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    appointments.value     = response.data.appointments;
    pagination.value.total = response.data.totalRows;
    scheduled.value        = response.data.scheduled;
    confirmed.value        = response.data.confirmed;
    canceled.value         = response.data.canceled;
};

const handleConfirm = (id) => {
  // Guardamos el id pero NO abrimos aún el SweetAlert
    pendingId.value = id;
}

const cancelAppointment = async () => {
    if (!pendingId.value) return

    await nextTick()

    Swal.fire({
        title: 'Cancelar',
        text: 'Por favor, indica el motivo de la cancelación',
        input: 'textarea',
        inputPlaceholder: 'Escribe el motivo aquí...',
        inputAttributes: {
            'aria-label': 'Motivo de cancelación'
        },
        showCancelButton: true,
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cerrar',
        confirmButtonColor: "#3085d6",
        reverseButtons: true,
        preConfirm: (motivo) => {
            if (!motivo || motivo.trim() === '') {
                Swal.showValidationMessage(
                    'Debes ingresar un motivo de cancelación'
                )
            }
            return motivo
        }
    }).then(async (result) => {
        if (result.isConfirmed) {
            const response = await apiClient('admin/statusAppointment', 'PUT', {id: pendingId.value, status: 2, observations: result.value});
            if (response.error) {
                showNotification(response.msj, '¡Error!', 'error');
                return
            }
            showNotification(response.msj);
            getAppointments();
            pendingId.value = null;
        }
    });
};

const reactivateAppointment = async (id) => {
    const response = await apiClient('admin/statusAppointment', 'PUT', {id, status: 1});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
    getAppointments();
}
const confirmAppointment = async (id) => {
    const response = await apiClient('admin/statusAppointment', 'PUT', {id, status: 4});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
    getAppointments();
}

const deleteAppointment = async (id) => {
    const response = await apiClient(`admin/appointment/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
    getAppointments();
}

const formatDate = (date) => {
    if (!date) return ''
    return date.toISOString().split('T')[0]
};

const setClass = (_status) => {
    switch (_status) {
        case 'Cancelada':
        case 'Eliminada':
            return 'text-danger'
        case 'Confirmada':
            return 'text-primary'
        case 'Agendada':
            return 'text-warning';
        case 'Finalizada':
            return 'text-success';
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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const sendMessage = (_a) => {
    const phone = `52${_a.customer.phone}`;
    
    const message = [
        `Hola ${_a.customer.name}, este es un recordatorio de tu cita el día *${dateEs(_a.date, ' de ', 0, true)} a las ${time(_a.horary)}*.`,
        `Te pedimos llegar 15 minutos antes del horario mencionado para brindarte un mejor servicio.`,
        `El total a pagar de tu cita es de *${formatCurrency(_a.cost)}*.`,
        `Por favor *confirma tu asistencia* por este medio.`,
        `Es un placer atenderte.`
    ].join('\n');
    
    const url = `https://wa.me/${phone}?text=${encodeURIComponent(message)}`;

    window.open(url, '_blank');
};

const resetFilters = () => {
    search.value.currentDate = formatDate(new Date());
    search.value.customer    = '';
    search.value.user        = '';
    search.value.status      = '';
    getAppointments();
};
</script>

<template>
    <Layout :menu="menu" :background="false">
        <el-col :span="24">
            <el-row :gutter="20">
                <el-col :span="18">
                    <el-card class="pt-0 pb-6">
                        <el-row>
                            <el-col :span="12" class="mb-4">
                                <h5>Citas {{ dateEs(search.currentDate, '/', 1, true) }}</h5>
                                <p class="text-secondary fs-small mb-0">
                                    <span>Agendadas: <span class="card-warning pt-1 pb-1 ps-3 pe-3 bold">{{ scheduled }}</span></span>
                                    <span class="ms-2">Confirmadas: <span class="card-info pt-1 pb-1 ps-3 pe-3 bold">{{ confirmed }}</span></span>
                                    <span class="ms-2">Canceladas: <span class="card-danger pt-1 pb-1 ps-3 pe-3 bold">{{ canceled }}</span></span>
                                </p>
                            </el-col>
                            <el-col :span="12" class="text-right mb-4">
                                <br>
                                <el-date-picker
                                    v-model="search.currentDate"
                                    type="date"
                                    format="DD/MM/YYYY"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Elige la fecha"
                                    :clearable="false"
                                    @change="getAppointments"
                                />
                                <el-tooltip
                                    class="box-item"
                                    effect="customized"
                                    content="Limpiar filtros"
                                    placement="top"
                                >
                                    <font-awesome-icon class="mt-2 ms-3 pointer" :icon="['fas', 'filter-circle-xmark']" @click="resetFilters" />
                                </el-tooltip>
                            </el-col>
                            <el-col :soan="24">
                                <table class="table table-striped fs-small">
                                    <thead>
                                        <tr>
                                            <th width="5%" class="text-center">#</th>
                                            <th width="20%">
                                                <el-input v-model="search.customer" placeholder="Cliente" title="Escribe para buscar" @input="getAppointments" clearable />
                                            </th>
                                            <th class="text-center">Hora de inicio</th>
                                            <th class="text-center">Total a pagar</th>
                                            <th width="20%">
                                                <el-input v-model="search.user" placeholder="Agendada por" title="Escribe para buscar" @input="getAppointments" clearable />
                                            </th>
                                            <th>Observaciones</th>
                                            <th class="text-center" width="10%">
                                                <el-select v-model="search.status" placeholder="Estatus" @change="getAppointments" clearable>
                                                    <el-option v-for="s in status" :key="s.id" :value="s.id" :label="s.name" />
                                                </el-select>
                                            </th>
                                            <th width="15%" class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="!appointments.length">
                                            <td class="text-center" colspan="8">
                                                Ningún dato disponible en esta tabla
                                            </td> 
                                        </tr>
                                        <tr v-for="a in appointments">
                                            <td class="text-center">{{ a.id }}</td>
                                            <td>{{ a.customer.name }}</td>
                                            <td class="text-center">{{ time(a.horary) }}</td>
                                            <td class="text-center">
                                                <!-- <span class="badge text-bg-primary mr-2 mb-1" v-for="s in a.services">{{ s.name }}</span> -->
                                                 {{ formatCurrency(a.cost) }}
                                            </td>
                                            <td>{{ a.created_by.name }}</td>
                                            <td>
                                                {{ a.observations ? a.observations : '' }} 
                                                <p v-if="a.appointment_status_id === 2" class="mb-0"><b>Canceló: </b>{{ a.updated_by.name }}</p>
                                            </td>
                                            <td class="text-center bold" :class="setClass(a.status.name)">{{ a.status.name }}</td>
                                            <td class="text-center">
                                                <el-button-group>
                                                    <el-tooltip v-if="a.appointment_status_id === 1" content="Enviar msj. para confirmación" effect="customized" placement="top">
                                                        <el-button
                                                            type="success"
                                                            @click="sendMessage(a)"
                                                        >
                                                            <font-awesome-icon :icon="['fab', 'whatsapp']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-tooltip v-if="a.appointment_status_id === 2" content="Reactivar cita" effect="customized" placement="top">
                                                        <el-button
                                                            type="success"
                                                            @click="reactivateAppointment(a.id)"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'redo']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-tooltip v-if="a.appointment_status_id === 1" content="Marcar como confirmada" effect="customized" placement="top">
                                                        <el-button
                                                            color="#626aef"
                                                            @click="confirmAppointment(a.id)"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'check']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-tooltip v-if="a.appointment_status_id === 4" content="Realizar venta" effect="customized" placement="top">
                                                        <el-button
                                                            type="success"
                                                            @click="newSale(a.id)"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'dollar-sign']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-tooltip v-if="[1, 4].includes(a.appointment_status_id)" content="Editar cita" effect="customized" placement="top">
                                                        <el-button
                                                            type="primary"
                                                        >
                                                            <font-awesome-icon :icon="['fas', 'pen']" />
                                                        </el-button>
                                                    </el-tooltip>
                                                    <el-popconfirm
                                                        v-if="[1, 4].includes(a.appointment_status_id)"
                                                        class="box-item"
                                                        confirm-button-text="Si"
                                                        cancel-button-text="No"
                                                        :hide-icon="true"
                                                        confirm-button-type="danger"
                                                        cancel-button-type="primary"
                                                        :width="200"
                                                        title="¿Seguro que deseas cancelar esta cita?"
                                                        placement="left"
                                                        @confirm="handleConfirm(a.id)"
                                                        @hide="cancelAppointment"
                                                    >
                                                        <template #reference>
                                                            <span>
                                                                <el-tooltip content="Cancelar cita" effect="customized" placement="top" :trigger="['hover']">
                                                                    <el-button type="warning" style="border-radius: 0px;">
                                                                        <font-awesome-icon :icon="['fas', 'times']" />
                                                                    </el-button>
                                                                </el-tooltip>
                                                            </span>
                                                        </template>
                                                    </el-popconfirm>
                                                    <el-popconfirm
                                                        v-if="[1, 4].includes(a.appointment_status_id)"
                                                        class="box-item"
                                                        confirm-button-text="Eliminar"
                                                        cancel-button-text="Cancelar"
                                                        :hide-icon="true"
                                                        confirm-button-type="danger"
                                                        cancel-button-type="primary"
                                                        :width="200"
                                                        title="¿Seguro que deseas eliminar esta cita?"
                                                        placement="right"
                                                        @confirm="deleteAppointment(a.id)"
                                                    >
                                                        <template #reference>
                                                            <span>
                                                                <el-tooltip content="Eliminar cita" effect="customized" placement="top">
                                                                    <el-button type="danger" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
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
                                    :page-sizes="[20, 40, 60, 80, 100]"
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
                    <el-card class="pt-0 ps-6 pe-6 pb-0">
                        <el-row :gutter="20">
                            <el-col :span="24" class="text-center mb-4 text-secondary">
                                <h5>Accesos rápidos</h5>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div class="card-warning p-4" @click="newAppoiment()">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'clipboard']" /></h1>
                                    <h6 class="text-center">Agendar cita</h6>
                                </div>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div class="card-success p-4" @click="newAppoiment()">
                                    <h1 class="text-center"><font-awesome-icon :icon="['far', 'calendar-alt']" /></h1>
                                    <h6 class="text-center">Agenda</h6>
                                </div>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div  class="card-danger p-4">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'dollar-sign']" /></h1>
                                    <h6 class="text-center">Venta rápida</h6>
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
    <CreateAppointment ref="createAppoimentRef" :get-parent-appointments="getAppointments" :service-type="serviceType" />
    <CreateSale ref="createSaleRef" :get-parent-appointments="getAppointments" :payment-methods="paymentMethods" />
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
.card-success {
    color: #1BC5BD;
    background-color: #C9F7F5 ;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-success:hover {
    background-color: #1BC5BD;
    color: white;
}
</style>