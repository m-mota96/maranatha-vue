<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose, computed } from 'vue';
import { format as formatDates } from 'date-fns';
import UploadImages from './UploadImages.vue';

const { paymentMethods, getParentAppointments } = defineProps({
    paymentMethods: {
        type: Array,
        required: true
    },
    getParentAppointments: {
        type: Function,
        required: true
    }
});

const uploadImagesRef     = ref(null);
const dialogVisible       = ref(false);
const appointmentId       = ref(null);
const allServices         = ref([]);
const servicesAndProducts = ref([]);
const staff               = ref([]);
const currentHour         = ref('');
const loading             = ref(false);
const disabledCash        = ref(false);
const information         = ref({
    appointment_id: null,
    subtotal: 0,
    total: 0,
    type_discount: 'amount',
    discount: 0,
    cash: 0,
    change: 0,
    payment_method: '',
    amount_cash: 0,
    amount_card: 0,
    observations: ''
});
const errors = ref({
    records: false,
    type: [],
    service_id: [],
    product_id: [],
    staff_id: [],
    start_time: [],
    end_time: []
});

const registerSale = async () => {
    const data = {
        services: JSON.stringify(servicesAndProducts.value.filter(sp => {
            return (sp.type === 'Servicio' && sp.newRecord) || (sp.type === 'Servicio' && !sp.newRecord && sp.deleted)
        })),
        products: JSON.stringify(servicesAndProducts.value.filter(sp => sp.type === 'Producto')),
        sale: JSON.stringify(information.value)
    };
    if (information.value.payment_method === 4) { // Transferencia
        uploadImagesRef.value?.uploadInfo(data);
    } else {
        if (validate()) {
            const response = await apiClient('admin/sale', 'POST', data);
            if (response.error) {
                showNotification(response.msj, '¡Error!', 'error', 9000);
                return
            }
            getParentAppointments();
            showNotification(response.msj);
            closeModal();
        }
    }
};

const validate = () => {
    resetErrors();
    let valid = true;
    if (!servicesAndProducts.value.length) {
        showNotification('Debes agregar al menos un servicio o producto.', '¡Error!', 'error', 8000);
        return false;
    }
    servicesAndProducts.value.forEach((sp, i) => {
        if (sp.newRecord) {
            if (!sp.type) {
                errors.value.type[i] = true;
                valid                = false;
            } else {
                if (sp.type === 'Servicio' && !sp.service_id) {
                    errors.value.service_id[i] = true;
                    valid                      = false;
                }
                if (sp.type === 'Servicio' && !sp.staff_id ) {
                    errors.value.staff_id[i] = true;
                    valid                    = false;
                }
                if (sp.type === 'Servicio' && !sp.start_time ) {
                    errors.value.start_time[i] = true;
                    valid                      = false;
                }
                if (sp.type === 'Servicio' && !sp.end_time ) {
                    errors.value.end_time[i] = true;
                    valid                    = false;
                }
                if (sp.type === 'Producto' && !sp.product_id) {
                    errors.value.product_id[i] = true;
                    valid                      = false;
                }
            }
        }
    });
    return valid;
};

const resetErrors = () => {
    errors.value.records    = false;
    errors.value.type       = [];
    errors.value.service_id = [];
    errors.value.product_id = [];
    errors.value.staff_id   = [];
    errors.value.start_time = [];
    errors.value.end_time   = [];
};

const showModal = async (_appointmentId) => {
    resetForm();
    getServices();
    getStaff();
    appointmentId.value              = _appointmentId;
    information.value.appointment_id = _appointmentId;
    await getAppointment(_appointmentId);
    dialogVisible.value = true;
};

const getAppointment = async (_appointmentId) => {
    const response = await apiClient(`admin/appointment/${_appointmentId}`);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    servicesAndProducts.value = response.data;
    calculateTotal();
};

const getServices = async () => {
    const response = await apiClient('admin/allServices');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    allServices.value = response.data;
}

const getStaff = async () => {
    const response = await apiClient('admin/allStaff');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    staff.value = response.data;
}

const addService = () => {
    currentHour.value = formatDates(new Date, 'HH:mm');
    servicesAndProducts.value.push({
        id: Date.now().toString(36) + Math.random().toString(36).substring(2),
        type: '',
        appointment_id: appointmentId.value,
        service_id: '',
        product_id: '',
        product_name: '',
        staff_id: '',
        price: '',
        start_time: '',
        end_time: '',
        quantity: 1,
        require_staff: false,
        newRecord: true,
        deleted: false
    });
}

const resetForm = () => {
    resetErrors();
    appointmentId.value              = null;
    servicesAndProducts.value        = [];
    information.value.appointment_id = null;
    information.value.subtotal       = 0;
    information.value.total          = 0;
    information.value.type_discount  = 'amount';
    information.value.discount       = 0;
    information.value.cash           = 0;
    information.value.change         = 0;
    information.value.payment_method = '';
    information.value.amount_cash    = 0;
    information.value.amount_card    = 0;
    information.value.observations   = '';
    paymentMethods.forEach(p => {
        if (p.default) {
            information.value.payment_method = p.id;
        }
    });
}

const deleteRecord = (_id, _newRecord) => {
    if (!_newRecord) {
        const index = servicesAndProducts.value.findIndex(sp => sp.id === _id);
        servicesAndProducts.value[index].deleted = true;
    } else {
        servicesAndProducts.value = servicesAndProducts.value.filter(sp => sp.id !== _id);
    }
    calculateTotal();
};

const filterRecords = computed(() => {
    return servicesAndProducts.value.filter(sp => {
        return sp.deleted === false;
    });
});

const setPrice = (allServiceId, serviceId) => {
    let filteredService = null;
    if (allServiceId) {
        filteredService = allServices.value.find((s) => s.id === allServiceId);
    }
    const index                                    = servicesAndProducts.value.findIndex(sp => sp.id === serviceId);
    servicesAndProducts.value[index].service_id    = filteredService ? filteredService.id : null;
    servicesAndProducts.value[index].price         = filteredService ? filteredService.price : 0;
    servicesAndProducts.value[index].require_staff = filteredService ? filteredService.require_staff : false;
    calculateTotal();
}

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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const resetInfo = (id) => {
    const index                                    = servicesAndProducts.value.findIndex(sp => sp.id === id);
    servicesAndProducts.value[index].price         = 0;
    servicesAndProducts.value[index].quantity      = 1;
    servicesAndProducts.value[index].require_staff = false;
    calculateTotal();
}

const calculateTotal = () => {
    let amount = 0;
    servicesAndProducts.value.forEach(sp => {
        if (!sp.deleted && (sp.service_id || sp.product_id)) {
            const price = sp.type === 'Servicio' ? parseInt(sp.price) : (parseInt(sp.price) * sp.quantity);
            amount      = amount + price;
        }
    });
    information.value.subtotal = amount;
    information.value.total    = amount;
};

const calculateChange = (_value) => {
    information.value.change   = 0;
    if (_value > information.value.total) {
        information.value.change = _value - information.value.total;
    }
};

const calculateDiscount = (_value) => {
    information.value.total = information.value.subtotal;
    if (_value) {
        const discount = information.value.type_discount === 'amount' ? parseInt(_value) : (information.value.subtotal * (parseInt(_value) / 100));
        information.value.total = information.value.subtotal - discount;
    }
    checkPaymentMethod(information.value.payment_method);
};

const calculateAmountCard = (_value) => {
    information.value.amount_card = information.value.total;
    if (_value) {
        information.value.amount_card = information.value.total - _value;
    }
};

const changeTypeDiscount = () => {
    information.value.discount = 0;
    information.value.total    = information.value.subtotal;
};

const checkPaymentMethod = (_value) => {
    information.value.cash        = 0;
    information.value.change      = 0;
    information.value.amount_cash = 0;
    information.value.amount_card = 0;
    disabledCash.value            = false;
    switch (_value) {
        case 2: // Efectivo y tarjeta
        case 3: // Tarjeta
            disabledCash.value            = true;
            information.value.amount_card = information.value.total;
            break;
        case 4: // Transferencia
            disabledCash.value = true;
            break;
    }
};

const filterStaffByService = (service_id) => {
    return staff.value.filter(person => 
        person.services.some(service => service.id === service_id)
    );
};

const querySearch = async (queryString, cb) => {
    if (queryString.length < 3) {
        cb([]);
        return;
    }

    const response = await apiClient('admin/product', 'GET', { name: queryString });
    const results  = response.data
    .filter(createFilter(queryString))
    .map(product => ({
        ...product,
        value: `
            ${product.name} ${product.content ? product.content : ''} ${product.abreviation ? product.abreviation : ''} ${product.brand ? '('+product.brand+')' : ''}
        `
    }));

    cb(results);
};

const createFilter = (queryString) => {
    const search = queryString.toLowerCase();

    return (product) => {
        return product.name.toLowerCase().includes(search);
    };
};

const handleSelect = (_product, id)=> {
    const index                                 = servicesAndProducts.value.findIndex(sp => sp.id === id);
    servicesAndProducts.value[index].product_id = _product.id;
    servicesAndProducts.value[index].price      = _product.price;
    calculateTotal();
};

const closeModal = () => {
    dialogVisible.value = false;
    resetForm();
}

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        title="Registrar venta"
        width="95%"
        style="margin-top: 1% !important;"
    >
        <el-row :gutter="30" class="mb-4">
            <el-col :span="24" class="mb-2">
                <h4 class="text-black text-center">Servicios y/o Productos</h4>
                <table class="my-table w-100">
                    <thead>
                        <tr>
                            <th class="!bg-blue-100 text-dark bold w-10">Tipo</th>
                            <th class="!bg-blue-100 text-dark bold 20">Servicio/Producto</th>
                            <th class="!bg-blue-100 text-dark bold 20">Staff</th>
                            <th class="!bg-blue-100 text-dark bold text-center">Hora inicial</th>
                            <th class="!bg-blue-100 text-dark bold text-center">Hora final</th>
                            <th class="!bg-blue-100 text-dark bold text-center">Precio</th>
                            <th class="!bg-blue-100 text-dark bold text-center w-10">Cantidad</th>
                            <th class="!bg-blue-100 text-dark bold text-center">Importe</th>
                            <th class="!bg-blue-100 text-dark bold text-center">
                                <el-tooltip content="Añadir servicio o producto" effect="customized" placement="top">
                                    <el-button class="btn-success" size="small" @click="addService">
                                        <font-awesome-icon :icon="['fas', 'plus']" />
                                    </el-button>
                                </el-tooltip>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(s, i) in filterRecords" :key="i">
                            <td>
                                <span v-if="!s.newRecord">{{ s.type }}</span>
                                <el-select
                                    v-if="s.newRecord"
                                    v-model="s.type"
                                    placeholder="Elige una opción"
                                    @change="resetInfo(s.id)"
                                    class="el-form-item"
                                    :class="{'is-error': errors.type[i]}"
                                >
                                    <el-option value="Producto" label="Producto" />
                                    <el-option value="Servicio" label="Servicio" />
                                </el-select>
                            </td>
                            <td>
                                <span v-if="!s.newRecord">{{ s.service.name }} ({{ s.service.time }} min.)</span>
                                <el-select
                                    v-if="s.newRecord && s.type === 'Servicio'"
                                    v-model="s.service_id"
                                    class="el-form-item w-100"
                                    :class="{'is-error': errors.service_id[i]}"
                                    @change="(val) => setPrice(val, s.id)"
                                    placeholder="Elige un servicio"
                                    clearable
                                    :filterable="true"
                                >
                                    <el-option v-for="serv in allServices" :key="serv.id" :value="serv.id" :label="serv.name" />
                                </el-select>
                                <el-autocomplete
                                    v-if="s.newRecord && s.type === 'Producto'"
                                    v-model="s.product_name"
                                    class="el-form-item w-100 text-left"
                                    :class="{'is-error': errors.product_id[i]}"
                                    :fetch-suggestions="querySearch"
                                    :trigger-on-focus="false"
                                    clearable
                                    placeholder="Escribe para buscar"
                                    @select="(val) => handleSelect(val, s.id)"
                                />
                            </td>
                            <td>
                                <span v-if="s.staff && !s.newRecord">{{ s.staff.name }} {{ s.staff.first_name }} {{ s.staff.lat_name }}</span>
                                <span v-if="!s.staff && !s.newRecord">N/A</span>
                                <el-select 
                                    v-if="s.newRecord && s.type === 'Servicio' && s.require_staff"
                                    v-model="s.staff_id"
                                    class="el-form-item w-100"
                                    :class="{'is-error': errors.staff_id[i]}"
                                    placeholder="Elige una persona de staff"
                                    clearable
                                    :filterable="true"
                                >
                                    <el-option v-for="st in filterStaffByService(s.service_id)" :key="st.id" :value="st.id" :label="`${st.name} ${st.first_name} ${st.last_name}`" />
                                </el-select>
                                <span v-if="s.newRecord && (s.type === 'Producto' || !s.require_staff)">N/A</span>
                            </td>
                            <td class="text-center w-10">
                                <span v-if="!s.newRecord">{{ to12HourFormat(s.start_time) }}</span>
                                <el-time-select
                                    v-if="s.newRecord && s.type === 'Servicio'"
                                    v-model="s.start_time"
                                    class="el-form-item w-100"
                                    :class="{'is-error': errors.start_time[i]}"
                                    start="08:00"
                                    step="00:5"
                                    :end="currentHour"
                                    placeholder="Hora"
                                    format="hh:mm A"
                                />
                                <span v-if="s.newRecord && s.type === 'Producto'">--:--</span>
                            </td>
                            <td class="text-center w-10">
                                <span v-if="!s.newRecord">{{ to12HourFormat(s.end_time) }}</span>
                                <el-time-select
                                    v-if="s.newRecord && s.type === 'Servicio'"
                                    v-model="s.end_time"
                                    class="el-form-item w-100"
                                    :class="{'is-error': errors.end_time[i]}"
                                    start="08:00"
                                    step="00:5"
                                    :end="currentHour"
                                    placeholder="Hora"
                                    format="hh:mm A"
                                />
                                <span v-if="s.newRecord && s.type === 'Producto'">--:--</span>
                            </td>
                            <td class="text-center">{{ formatCurrency(s.price) }}</td>
                            <td class="text-center">
                                <span v-if="s.type === 'Servicio'">1</span>
                                <!-- <span v-if="s.type === 'Producto'"></span> -->
                                <el-input-number
                                    v-if="s.newRecord && s.type === 'Producto'"
                                    v-model="s.quantity"
                                    class="w-100"
                                    :precision="0"
                                    :step="1"
                                    :min="1"
                                    @change="calculateTotal"
                                />
                            </td>
                            <td class="text-center">
                                <span v-if="s.type === 'Servicio'">{{ formatCurrency(s.price) }}</span>
                                <span v-if="s.type === 'Producto'">{{ formatCurrency(s.price * s.quantity) }}</span>
                            </td>
                            <td class="text-center w-10">
                                <el-tooltip :content="s.type === 'Servicio' ? `Eliminar servicio` : `Eliminar producto`" effect="customized" placement="left">
                                    <el-button class="btn-danger" size="small" @click="deleteRecord(s.id, s.newRecord)">
                                        <font-awesome-icon :icon="['fas', 'trash-can']" />
                                    </el-button>
                                </el-tooltip>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </el-col>
            <el-col :span="4">
                <h5 class="text-dark normal">Método de pago</h5>
                <el-radio-group v-model="information.payment_method" @change="checkPaymentMethod">
                    <div class="w-100" v-for="pm in paymentMethods" :key="pm.id">
                        <el-radio :value="pm.id" class="text-dark">{{ pm.name }}</el-radio><br>
                    </div>
                </el-radio-group>
            </el-col>
            <el-col :span="4">
                <div class="pr mb-1">
                    <span class="text-dark normal text-lg">Subtotal:</span>
                    <span class="text-dark normal text-lg pa" style="right: 0;">{{ formatCurrency(information.subtotal) }}</span>
                </div>
                <div class="pr mb-2">
                    <span class="text-dark normal text-lg">Descuento:</span>
                    <el-input
                        v-model="information.discount"
                        class="input-with-select"
                        size="small"
                        @input="calculateDiscount"
                    >
                        <template #append>
                            <el-select v-model="information.type_discount" placeholder="Elige una opción" style="width: 100px" size="small" @change="changeTypeDiscount" >
                                <el-option label="pesos" value="amount" />
                                <el-option label="%" value="percentage" />
                            </el-select>
                        </template>
                    </el-input>
                </div>
                <div class="pr mb-2">
                    <span class="text-dark bold text-lg">Total:</span>
                    <span class="text-dark bold text-lg pa" style="right: 0;">{{ formatCurrency(information.total) }}</span>
                </div>
                <el-divider class="mb-2 mt-2" />
                <div class="pr mb-1">
                    <span class="text-dark normal text-lg">Pagó con:</span>
                    <el-input-number
                        v-model="information.cash"
                        class="w-100"
                        :precision="0"
                        :step="1"
                        :min="0"
                        :controls="false"
                        size="small"
                        @input="calculateChange"
                        :disabled="disabledCash"
                    />
                </div>
                <div class="pr mb-1">
                    <span class="text-dark normal text-lg">Cambio:</span>
                    <span class="text-dark normal text-lg pa" style="right: 0;">{{ formatCurrency(information.change) }}</span>
                </div>
            </el-col>
            <el-col :span="4" v-if="[2, 3].includes(information.payment_method)">
                <h6 v-if="information.payment_method === 2" class="text-dark bold">¿Cuánto pagó en efectivo?</h6>
                <el-input-number
                    v-if="information.payment_method === 2"
                    v-model="information.amount_cash"
                    class="w-100 mb-2"
                    :precision="0"
                    :step="1"
                    :min="0"
                    :controls="false"
                    size="small"
                    @input="calculateAmountCard"
                />
                <h6 v-if="[2, 3].includes(information.payment_method)" class="text-dark bold mb-1">Cargo a la tarjeta:</h6>
                <h5 v-if="[2, 3].includes(information.payment_method)" class="!text-blue-500 bold">{{ formatCurrency(information.amount_card) }}</h5>
            </el-col>
            <el-col :span="8" v-if="information.payment_method === 4">
                <h6 class="text-dark bold">Comprobante de transferencia:</h6>
                <UploadImages
                    ref="uploadImagesRef"
                    v-model="loading"
                    :get-parent-appointments="getParentAppointments"
                    :close-parent-modal="closeModal"
                    :validate-parent="validate"
                    url="admin/sale"
                />
            </el-col>
        </el-row >
        <template #footer>
            <div class="dialog-footer">
                <el-button type="danger" plain @click="dialogVisible = false" :disabled="loading">Cancelar venta</el-button>
                <el-button type="success" plain @click="registerSale" :loading="loading">
                    Registrar venta
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
.my-table {
    border: 1px solid;
    border-radius: 5px;
    border-collapse: separate; /* Obligatorio para que funcione el radius */
    border-spacing: 0; 
    overflow: hidden;
}
.my-table th {
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 5px;
    padding-bottom: 5px;
}
.my-table td {
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 5px;
    padding-bottom: 5px;
}
</style>