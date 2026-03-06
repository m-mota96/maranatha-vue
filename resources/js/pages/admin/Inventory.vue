<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateInventory from './modals/CreateInventory.vue';
import { dateEs } from '@/dateEs';
import { format as formatDates } from 'date-fns';

const { module, menu, references } = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array,
        required: true
    },
    references: {
        type: Array,
        required: true
    }
});

const createInventoryRef = ref(null);
const inventories        = ref([]);
const pagination         = ref({
    currentPage: 1,
    pageSize: 50,
    total: 0
});
const search = ref({
    product_name: '',
    type: '',
    reference_id: '',
    product_cost: '',
    dates: []
});
const order = ref({
    orderBy: 'created_at',
    order: 'DESC'
});

onMounted(() => {
    setDates();
    getInventory();
});

const getInventory = async () => {
    const diference = calculateDiference(search.value.dates[0], search.value.dates[1]);
    if (diference > 3) {
        showNotification('El rango máximo de fechas es de 3 meses.', '¡Atención!', 'warning', 9000);
        setDates();
        return false;
    }
    const response = await apiClient('admin/inventories', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    inventories.value = response.data.inventories;
    pagination.value.total = response.data.totalRows;
};

const deleteInventory = async (id) => {
    const response = await apiClient(`admin/inventory/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    getInventory();
    showNotification(response.msj);
};

const openModal = () => {
    createInventoryRef.value?.showModal();
};

const resetFilters = () => {
    search.value.product_name = '';
    search.value.type         = '';
    search.value.reference_id = '';
    search.value.product_cost = '';
    setDates();
    order.value.orderBy       = 'created_at';
    order.value.order         = 'DESC';
    getInventory();
}

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
    getInventory();
};

const handleCurrentChange = (val) => {
    getInventory();
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
                @change="getInventory"
            />
        </el-col>
        <el-col class="mb-2" :span="4" :offset="10">
            <label for="order">Ordenar por</label>
            <el-select v-model="order.orderBy" @change="getInventory" id="order">
                <el-option :key="0" label="Fecha de creación" value="created_at" />
                <el-option :key="1" label="Cantidad" value="quantity" />
                <el-option :key="2" label="Costo del producto" value="product_cost" />
                <el-option :key="3" label="Fecha de caducidad" value="expiration_date" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getInventory">
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
            <el-table :data="inventories" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column>
                    <template #header>
                        <el-input placeholder="Producto" title="Escribe para buscar" v-model="search.product_name" @input="getInventory" clearable />
                    </template>
                    <template #default="scope">
                        {{ scope.row.product.name }} 
                        {{ scope.row.product.content ? scope.row.product.content : '' }} 
                        {{ scope.row.product.abreviation ? scope.row.product.abreviation : '' }}
                        {{ scope.row.product.brand ? '('+scope.row.product.brand+')' : '' }}
                    </template>
                </el-table-column>
                <el-table-column>
                    <template #header>
                        <el-select v-model="search.type" placeholder="Tipo de movimiento" @change="getInventory" clearable>
                            <el-option :key="0" value="input" label="Ingreso" />
                            <el-option :key="1" value="output" label="Egreso" />
                        </el-select>
                    </template>
                    <template #default="scope">
                        {{ scope.row.type === 'input' ? 'Ingreso' : 'Egreso' }}
                    </template>
                </el-table-column>
                <el-table-column prop="reference.name">
                    <template #header>
                        <el-select v-model="search.reference_id" placeholder="Referencia" @change="getInventory" clearable>
                            <el-option v-for="r in references" :key="r.id" :value="r.id" :label="r.name" />
                        </el-select>
                    </template>
                </el-table-column>
                <el-table-column label="Cantidad" align="center">
                    <template #default="scope">
                        {{ scope.row.product.type_sale === 'pza' ? parseInt(scope.row.quantity) : scope.row.quantity }}
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-input placeholder="Costo del producto" title="Escribe para buscar" v-model="search.product_cost" @input="getInventory" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.product_cost) }}
                    </template>
                </el-table-column>
                <el-table-column prop="batch" label="Lote" />
                <el-table-column prop="expiration_date" label="Fecha de caducidad" />
                <el-table-column prop="description" label="Descripción" />
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
                <el-table-column width="80" align="center">
                    <template #header>
                        <el-tooltip content="Nuevo producto" effect="customized" placement="top">
                            <el-button class="btn-success" @click="openModal()">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <!-- <el-tooltip content="Editar producto" effect="customized" placement="top">
                                <el-button class="btn-success" @click="openModal(scope.row)">
                                    <font-awesome-icon :icon="['fas', 'pen']" />
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
                                title="¿Seguro que deseas eliminar este producto?"
                                placement="left"
                                @confirm="deleteInventory(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar producto" effect="customized" placement="top">
                                            <el-button class="btn-danger">
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
                :page-sizes="[50, 100, 150, 200, 250]"
                layout="sizes, prev, pager, next"
                :total="pagination.total"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
        </el-col>
    </Layout>
    <CreateInventory ref="createInventoryRef" :get-parent-inventory="getInventory" :references="references" />
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>