<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { dateEs } from '@/dateEs';
import Swal from 'sweetalert2';
import { format as formatDates } from 'date-fns';
import DetailSale from './modals/DetailSale.vue';

const { module, menu, statusSales, paymentMethods } = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array,
        required: true
    },
    statusSales: {
        type: Array,
        required: true
    },
    paymentMethods: {
        type: Array,
        required: true
    }
});

const detailSaleRef = ref(null);
const sales         = ref([]);
const pagination    = ref({
    currentPage: 1,
    pageSize: 50,
    total: 0
});
const search = ref({
    customer: '',
    payment_method: '',
    subtotal: '',
    discount: '',
    type_discount: '',
    total: '',
    user_register: '',
    user_cancel: '',
    status: '',
    dates: []
});
const order = ref({
    orderBy: 'created_at',
    order: 'DESC'
});

onMounted(() => {
    setDates();
    getSales();
});

const getSales = async () => {
    const diference = calculateDiference(search.value.dates[0], search.value.dates[1]);
    if (diference > 3) {
        showNotification('El rango máximo de fechas es de 3 meses.', '¡Atención!', 'warning', 9000);
        setDates();
        return false;
    }
    const response = await apiClient('admin/sales', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    sales.value            = response.data.sales;
    pagination.value.total = response.data.totalRows;
};

const saleDetails = (id) => {
    detailSaleRef.value?.showModal(id);
};

const statusSale = async (_sale) => {
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
            const response = await apiClient('admin/sale', 'PUT', {id: _sale.id, status: 2, observations: result.value});
            if (response.error) {
                showNotification(response.msj, '¡Error!', 'error');
                return
            }
            getSales();
            showNotification(response.msj);
        }
    });
};

const resetFilters = () => {
    search.value.customer       = '';
    search.value.payment_method = '';
    search.value.subtotal       = '';
    search.value.discount       = '';
    search.value.type_discount  = '';
    search.value.total          = '';
    search.value.user_register  = '';
    search.value.user_cancel    = '';
    search.value.status         = '';
    setDates();
    order.value.orderBy           = 'created_at';
    order.value.order             = 'DESC';
    getSales();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const viewHorary = (_date) => {
    const newHour = _date.slice(-8);
    return to12HourFormat(newHour);
};

const handleSizeChange = (val) => {
    getSales();
};

const handleCurrentChange = (val) => {
    getSales();
};

const to12HourFormat = (horary) => {
    // Divido la cadena en horas y minutos
    let [hours, minutes] = horary.split(':');

    // Determino si es AM o PM
    const suffix = hours >= 12 ? 'PM' : 'AM';

    // Convierto la hora al formato de 12 horas (el resto de 12)
    // El caso de '00' se maneja convirtiéndolo a 12
    hours = (hours % 12) || 12;

    // Aseguro que las horas siempre tengan dos dígitos (ej. '05')
    const horasFormateadas = hours.toString().padStart(2, '0');

    return `${horasFormateadas}:${minutes} ${suffix}`;
};

const setDates = () => {
    const date     = new Date();
    const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
    const lastDay  = new Date(date.getFullYear(), date.getMonth() + 1, 0);
    
    search.value.dates = [formatDates(firstDay, 'yyyy-MM-dd'), formatDates(lastDay, 'yyyy-MM-dd')];
}

const calculateDiference = (startDate, endDate) => {
    const start = new Date(startDate);
    const end   = new Date(endDate);

    // Multiplicamos la diferencia de años por 12 y ajustamos con los meses
    let months = (end.getFullYear() - start.getFullYear()) * 12;
    months     -= start.getMonth();
    months     += end.getMonth();

    // Retornamos 0 si la fecha final es anterior a la inicial
    return months <= 0 ? 0 : months;
};
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col class="mb-2" :span="5">
            <label>Fecha de registro</label>
            <el-date-picker
                class="el-form-item mb-0 mt-1 w-100"
                v-model="search.dates"
                type="daterange"
                range-separator="A"
                start-placeholder="Fecha inicial"
                end-placeholder="Fecha final"
                placement="bottom-start"
                format="DD/MM/YYYY"
                value-format="YYYY-MM-DD"
                :clearable="false"
                @change="getSales"
            />
        </el-col>
        <el-col class="mb-2" :span="4" :offset="10">
            <label for="order">Ordenar por</label>
            <el-select v-model="order.orderBy" @change="getSales" id="order">
                <el-option :key="0" value="created_at" label="Fecha de creación" />
                <el-option :key="1" value="payment_method_id" label="Método de pago" />
                <el-option :key="2" value="subtotal" label="Subtotal" />
                <el-option :key="3" value="total" label="Total" />
                <el-option :key="4" value="status" label="Estatus" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getSales">
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
            <el-table :data="sales" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column prop="appointment.customer.name">
                    <template #header>
                        <el-input v-model="search.customer" placeholder="Cliente" @input="getSales" clearable />
                    </template>
                </el-table-column>
                <el-table-column prop="payment_method.name">
                    <template #header>
                        <el-select v-model="search.payment_method" placeholder="Método de pago" @change="getSales" clearable title="Método de pago">
                            <el-option v-for="p in paymentMethods" :key="p.id" :value="p.id" :label="p.name" />
                        </el-select>
                    </template>
                </el-table-column>
                <el-table-column>
                    <template #header>
                        <el-input v-model="search.subtotal" placeholder="Subtotal" @input="getSales" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.subtotal) }}
                    </template>
                </el-table-column>
                <el-table-column label="Descuento">
                    <template #default="scope">
                        <span v-if="scope.row.discount">
                            {{ scope.row.type_discount === 'amount' ? formatCurrency(scope.row.discount) : scope.row.discount }}{{ scope.row.type_discount === 'amount' ? ' pesos' : '%' }}
                        </span>
                    </template>
                </el-table-column>
                <el-table-column>
                    <template #header>
                        <el-input v-model="search.total" placeholder="Total" @input="getSales" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.total) }}
                    </template>
                </el-table-column>
                <el-table-column prop="observations" label="Observaciones" />
                <el-table-column prop="created_by.name">
                    <template #header>
                        <el-input v-model="search.user_register" placeholder="Usuario que registró" @input="getSales" clearable title="Usuario que registró" />
                    </template>
                </el-table-column>
                <el-table-column align="center">
                    <template #header>
                        Fecha y hora<br>de registro
                    </template>
                    <template #default="scope">
                        {{ dateEs(scope.row.created_at, '/', 1) }}
                        <br>
                        {{ viewHorary(scope.row.created_at) }}
                    </template>
                </el-table-column>
                <el-table-column prop="updated_by.name">
                    <template #header>
                        <el-input v-model="search.user_cancel" placeholder="Usuario que canceló" @input="getSales" clearable title="Usuario que canceló" />
                    </template>
                </el-table-column>
                <el-table-column align="center">
                    <template #header>
                        Fecha y hora<br>de cancelación
                    </template>
                    <template #default="scope">
                        <span v-if="scope.row.status_sale_id === 2">
                            {{ dateEs(scope.row.updated_at, '/', 1) }}
                            <br>
                            {{ viewHorary(scope.row.updated_at) }}
                        </span>
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-select placeholder="Estatus" v-model="search.status" @change="getSales" clearable>
                            <el-option v-for="s in statusSales" :key="s.id" :value="s.id" :label="s.name" />
                        </el-select>
                    </template>
                    <template #default="scope">
                        <span 
                            class="bold" 
                            :class="{'text-success': scope.row.status_sale_id === 1, 'text-danger': scope.row.status_sale_id === 2}"
                        >
                            {{ scope.row.status_sale.name }}
                        </span>
                    </template>
                </el-table-column>
                <el-table-column label="Acciones" width="150" align="center">
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Resumen de venta" effect="customized" placement="top">
                                <el-button
                                    type="primary"
                                    @click="saleDetails(scope.row.id)"
                                >
                                    <font-awesome-icon :icon="['fas', 'clipboard-list']" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip content="Cancelar venta" effect="customized" placement="top" v-if="scope.row.status_sale_id === 1">
                                <el-button
                                    type="danger"
                                    @click="statusSale(scope.row)"
                                >
                                    <font-awesome-icon :icon="['fas', 'x']" />
                                </el-button>
                            </el-tooltip>
                        </el-button-group>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                class="mt-3 custom-pager"
                v-model:current-page="pagination.currentPage"
                v-model:page-size="pagination.pageSize"
                :page-sizes="[50, 100, 150, 200, 250]"
                layout="sizes, prev, pager, next"
                :total="pagination.total"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
        </el-col>
    </Layout>
    <DetailSale ref="detailSaleRef" />
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>