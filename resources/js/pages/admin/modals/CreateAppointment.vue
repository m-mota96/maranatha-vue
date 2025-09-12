<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { addMinutesToTime } from '@/addMinutesToTime';
import { ref, defineExpose, onMounted, watch } from 'vue';
import CreateEditCustomer from './CreateEditCustomer.vue';
import { Timeline } from 'vue-timeline-chart';
import 'vue-timeline-chart/style.css';
// import 'vue-timeline-chart/dist/vue-timeline-chart.css';

const { serviceType } = defineProps({
    serviceType: {
        type: Array,
        required: true
    }
});

const createEditCustomerRef = ref(null);
const dialogVisible         = ref(false);
const title                 = ref('Registrar nueva cita');
const button                = ref('Guardar');
const staff                 = ref([]);
const quantityServices      = ref(1);
const services              = ref([]);
const servicesForStaff      = ref([]);
const form                  = ref({
    customer_id: null,
    customer: '',
    date: new Date(),
    dateFormatted: '',
    horary: '',
    service_type: [],
    services: []
});
const groups               = ref([]);
const items                = ref([]);
const timeline             = ref(null);
const initialViewportRange = { start: 400000, end: 700000 };
const viewport             = ref({ ...initialViewportRange });
const errors               = ref({
    customer: false,
    horary: false,
    horary_invalid: false,
});

onMounted(() => {
    form.value.dateFormatted = formatDate(form.value.date);
    resetViewport();
});

const saveAppointment = async () => {
    if (validate()) {
        const response = await apiClient('admin/appointment', 'POST', {
            customer_id: form.value.customer_id,
            date: form.value.dateFormatted,
            horary: to24HourFormat(form.value.horary),
            services: services.value
        });
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 8000);
            return
        }
        dialogVisible.value = false;
        showNotification(response.msj);
    }
};

const validate = () => {
    resetErrors();
    let valid = true;
    if (!form.value.customer_id) {
        errors.value.customer = true;
        valid                 = false;
    }
    if (!form.value.horary) {
        errors.value.horary = true;
        valid               = false;
    } else {
        const today = new Date();
        if (formatDate(form.value.date) === formatDate(today)) {
            const options = { hour: '2-digit', hour12: true, minute: '2-digit' };
            const formattedTime = today.toLocaleTimeString('en-US', options);
            if (form.value.horary.substring(0, 5) < formattedTime.substring(0, 5)) {
                errors.value.horary_invalid = true;
                valid                       = false;
            }
        }
    }
    return valid;
};

const showModal = () => {
    resetForm();
    searchStaff();
    for (let i = 0; i < serviceType.length; i++) {
        form.value.service_type.push('');
        form.value.services.push('');
    }
    dialogVisible.value = true;
};

const searchStaff = async (resetHorary = false) => {
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    if (form.value.date < today) {
        resetForm();
        showNotification('No puedes elegir fechas pasadas.', '¡Error!', 'error', 7500);
        return
    }
    const currentDate = form.value.date;
    currentDate.setHours(0, 0, 0, 0);
    if (resetHorary) form.value.horary = formatDate(currentDate) === formatDate(today) ? roundToStep(new Date(), 15) : '11:00 AM';
    errors.value.horary         = false;
    errors.value.horary_invalid = false;
    if (form.value.date && form.value.horary) {
        staff.value              = [];
        form.value.dateFormatted = formatDate(form.value.date);
        const response           = await apiClient('admin/searchStaff', 'GET', { date: form.value.dateFormatted, horary: form.value.horary });
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        staff.value  = response.data.staff;
        groups.value = [];
        items.value  = [];
        response.data.staff.forEach(s => {
            groups.value.push({id: s.id, label: s.name});
        });
        servicesForStaff.value = response.data.servicesForStaff;
        setServicesForStaff(response.data.servicesForStaff);
    }
};

const setServicesForStaff = (_servicesForStaff) => {
    _servicesForStaff.forEach(st => {
        items.value.push({
            group: st.staff_id,
            type: 'range',
            start: new Date(`${form.value.dateFormatted}T${st.start_time}`).getTime(),
            end: new Date(`${form.value.dateFormatted}T${st.end_time}`).getTime(),
            itemContent: st.service.name,
            cssVariables: { '--item-background': st.service.color }
        });
    });
};

const addService = () => {
    if (quantityServices.value < serviceType.length) {
        quantityServices.value++;
    }
};

const newCustomer = (data = null) => {
    createEditCustomerRef.value?.showModal(data);
};

const resetForm = () => {
    form.value.customer_id   = null;
    form.value.customer      = '';
    form.value.date          = new Date();
    form.value.dateFormatted = '';
    form.value.horary        = roundToStep(new Date(), 15);
    form.value.service_type  = [];
    form.value.services      = [];
    quantityServices.value   = 1;
    services.value           = [];
    items.value              = [];
    staff.value              = [];
    resetErrors();
};

const resetErrors = () => {
    errors.value.customer       = false;
    errors.value.horary         = false;
    errors.value.horary_invalid = false;
};

const getAvailableServiceTypes = (currentIndex) => {
    const selectedIds = form.value.service_type.filter((id, index) => index !== currentIndex && id != null);
    return serviceType.filter(s => !selectedIds.includes(s.id));
};

const validateService = (currentIndex) => {
    serviceType.forEach(s => {
        if (s.id == form.value.service_type[currentIndex]) {
            form.value.services[currentIndex] = JSON.parse(JSON.stringify(s.services));
        }
    });
};

const moreMinus = (value, index, currentIndex, info) => {
    let val          = form.value.services[index][currentIndex].quantity;
    val              = val + (value);
    if (val < 1) {
        val = 1;
        form.value.services[index][currentIndex].quantity = 1;
        return
    }
    form.value.services[index][currentIndex].quantity = val;
    previewServices(value, info);
};

const previewServices = (quantity, info, checked = true) => {
    let initialHorary = form.value.horary ? form.value.horary : '';
    if (checked) {
        switch (quantity) {
            case 1:
                services.value.push({
                    uid: generateUID(),
                    id: info.id,
                    name: info.name+` (${info.time} min.)`,
                    time: info.time,
                    staff_id: '',
                    initial_time: initialHorary,
                    final_time: initialHorary ? addMinutesToTime(initialHorary, info.time) : '--:--',
                    start_time: initialHorary ? to24HourFormat(initialHorary) : null,
                    end_time: initialHorary ? to24HourFormat(addMinutesToTime(initialHorary, info.time)) : null,
                    color: info.color,
                    price: info.price,
                    discounted_price: info.discounted_price
                });
                break;
            case -1:
                const index = services.value.map(s => s.id).lastIndexOf(info.id);
                if (index !== -1) {
                    services.value.splice(index, 1);
                }
                break;
        }
    } else {
        info.quantity  = 1;
        services.value = services.value.filter(s => s.id !== info.id);
    }
    setItemTimeLine();
};

const filterStaffByService = (service_id) => {
    return staff.value.filter(person => 
        person.services.some(service => service.id === service_id)
    );
};

const isStaffBusy = (staffId, initial_time, final_time) => {
    const allServices = [
        ...(services.value || []),
        ...(servicesForStaff.value || [])
    ];

    return allServices.some(s =>
        s.staff_id === staffId &&
        (
            (initial_time >= s.start_time && initial_time < s.end_time) ||
            (final_time > s.start_time && final_time <= s.end_time) ||
            (initial_time <= s.start_time && final_time >= s.end_time)
        )
    );
};

const editHorary = (horary, currentIndex) => {
    if (!horary) {
        services.value[currentIndex].staff_id     = '';
        services.value[currentIndex].initial_time = '--:--';
        services.value[currentIndex].final_time   = '--:--';
        services.value[currentIndex].start_time   = null;
        services.value[currentIndex].end_time     = null;
        return
    }
    services.value[currentIndex].initial_time = horary;
    services.value[currentIndex].final_time   = addMinutesToTime(horary, services.value[currentIndex].time);
    services.value[currentIndex].start_time   = to24HourFormat(horary);
    services.value[currentIndex].end_time     = to24HourFormat(addMinutesToTime(horary, services.value[currentIndex].time));
};

const setItemTimeLine = () => {
    items.value = [];
    setServicesForStaff(servicesForStaff.value);
    services.value.forEach(s => {
        if (s.staff_id) {
            items.value.push({
                group: s.staff_id,
                type: 'range',
                start: new Date(`${form.value.dateFormatted}T${s.start_time}`).getTime(),
                end: new Date(`${form.value.dateFormatted}T${s.end_time}`).getTime(),
                itemContent: s.name,
                cssVariables: { '--item-background': s.color }
            });
        }
    });
};

const resetViewport = () => {
    if (timeline.value) {
        timeline.value.setViewport(new Date(`${form.value.dateFormatted}T11:00`).getTime(), new Date(`${form.value.dateFormatted}T20:00`).getTime());
    }
};

const handleViewportChange = (newViewport) => {
    viewport.value = newViewport;
    resetViewport();
};

const roundToStep = (date, stepMinutes = 15) => {
    const hours = date.getHours();
    const minutes = date.getMinutes();
    // redondeo al múltiplo de step
    const roundedMinutes = Math.ceil(minutes / stepMinutes) * stepMinutes;
    
    let finalHours = hours;
    let finalMinutes = roundedMinutes;
    
    // si se pasa de 60 minutos, sumamos 1 hora
    if (roundedMinutes === 60) {
        finalHours += 1;
        finalMinutes = 0;
    }

    // conversión a 12h
    let hours12 = finalHours % 12;
    hours12 = hours12 === 0 ? 12 : hours12;
    const ampm = finalHours >= 12 ? "PM" : "AM";

    const hoursStr = hours12.toString().padStart(2, "0");
    const minutesStr = finalMinutes.toString().padStart(2, "0");

    return `${hoursStr}:${minutesStr} ${ampm}`;
};

const querySearchAsync = async (queryString, cb) => {
    if (!queryString) {
        cb([])
        return
    }

    const response = await apiClient('admin/customer', 'GET', {customer: form.value.customer});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 6000);
        cb([]);
        return
    }

    const results = response.data.map(item => ({
        value: item.name,
        id: item.id
    }));
    cb(results);
};

const handleSelect = (item) => {
    form.value.customer_id = item.id;
};

const formatDate = (date) => {
    if (!date) return ''
    return date.toISOString().split('T')[0]
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const to24HourFormat = (time12h) => {
    const [time, modifier] = time12h.split(' ');
    let [hours, minutes] = time.split(':').map(Number);

    if (modifier === 'PM' && hours < 12) hours += 12;
    if (modifier === 'AM' && hours === 12) hours = 0;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
};

const generateUID = () => {
  return Math.random().toString(36).substr(2, 9);
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="1800"
        style="margin-top: 1% !important;"
    >
    <el-row :gutter="10">
        <el-col :span="6">
            <el-col :span="24" class="mb-3">
                <p class="bold text-black relative mb-0">
                    Cliente 
                    <el-tooltip
                        placement="top"
                        content="Nuevo cliente"
                        effect="customized"
                    >
                        <font-awesome-icon class="my-btn absolute right" :icon="['fas', 'plus']" @click="newCustomer()" />
                    </el-tooltip>
                </p>
                <el-autocomplete
                    class="el-form-item"
                    :class="{'is-error': errors.customer}"
                    v-model="form.customer"
                    :fetch-suggestions="querySearchAsync"
                    placeholder="Escribe para buscar un cliente"
                    @select="handleSelect"
                    :trigger-on-focus="false"
                    :debounce="300"
                    clearable
                />
                <span class="text-danger fs-small" v-if="errors.customer">El cliente es obligatorio y debes elegir uno de la lista.</span>
            </el-col>
            <el-col :span="24">
                <p class="bold text-black mb-0">Fecha de cita</p>
                <el-calendar class="w-100" v-model="form.date" @update:modelValue="searchStaff(true)" />
            </el-col>
        </el-col>
        <el-col :span="6">
            <el-col :span="24" class="mb-3">
                <p class="bold text-black mb-0">Hora de cita</p>
                <el-time-select
                    class="el-form-item"
                    :class="{'is-error': errors.horary || errors.horary_invalid}"
                    v-model="form.horary"
                    start="11:00"
                    step="00:15"
                    end="20:00"
                    placeholder="Selecciona la hora"
                    format="hh:mm A"
                    @change="searchStaff()"
                />
                <span class="text-danger fs-small" v-if="errors.horary">La hora de la cita es obligatoria.</span>
                <span class="text-danger fs-small" v-if="errors.horary_invalid">La hora de la cita no puede ser anterior a la hora actual.</span>
            </el-col>
            <el-col :span="24">
                <p class="bold text-black mb-0 relative">
                    <span class="no-select">Servicios </span>
                    <el-tooltip
                        placement="top"
                        content="Añadir servicio"
                        effect="customized"
                    >
                    <font-awesome-icon class="my-btn absolute right" :icon="['fas', 'plus']" @click="addService()" />
                    </el-tooltip>
                </p>
                <div v-for="i in quantityServices" :key="i" class="mb-3">
                    <el-select v-model="form.service_type[i - 1]" @change="validateService(i - 1)">
                        <el-option v-for="s in getAvailableServiceTypes(i - 1)" :key="s.id" :label="s.name" :value="s.id" />
                    </el-select>
                    <!-- <p v-for="s in form.services[i - 1]" :key="s.id">{{ s.name }}</p> -->
                    <table class="table table-striped mt-1 w-100">
                        <tbody>
                            <tr v-for="(s, j) in form.services[i - 1]" :key="s.id">
                                <td class="p-0" style="width: 80%;">
                                    <el-checkbox
                                        class="bold text-dark mt-2"
                                        style="height: auto; white-space: normal;"
                                        v-model="s.active"
                                        :label="`${s.name} (${formatCurrency(s.price)} - ${s.time} min.)`"
                                        size="large"
                                        @change="(val) => previewServices(1, s, val)"
                                    >
                                        <span style="white-space: normal;">
                                            {{ s.name }} ({{ formatCurrency(s.price) }} - {{ s.time }} min.)
                                        </span>
                                    </el-checkbox>
                                </td>
                                <td class="p-o text-center" style="width: 20%;">
                                    <span v-if="s.active">
                                        <font-awesome-icon class="my-btn me-2" :icon="['fas', 'minus']" @click="moreMinus(-1, (i - 1), j, s)" />
                                        <span class="bold no-select">{{ s.quantity }}</span>
                                        <font-awesome-icon class="my-btn ms-2" :icon="['fas', 'plus']" @click="moreMinus(1, (i - 1), j, s)" />
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </el-col>
        </el-col>
        <el-col :span="12">
            <table class="table table-striped w-100" v-if="services.length">
                <thead>
                    <tr>
                        <th class="pt-0">Servicio</th>
                        <th class="pt-0 text-center">Hora inicial</th>
                        <th class="pt-0 text-center">Hora final</th>
                        <th class="pt-0">Persona que realiza el servicio</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(s, i) in services" :key="s.uid">
                        <td>{{ s.name }}</td>
                        <td class="text-center" width="150">
                            <span v-if="!form.horary">--:--</span>
                            <el-time-select
                                v-if="form.horary"
                                v-model="s.initial_time"
                                start="00:00"
                                step="00:10"
                                end="23:59"
                                placeholder="Selecciona la hora"
                                format="hh:mm A"
                                @change="(val) => editHorary(val, i)"
                            />
                        </td>
                        <td class="text-center" width="150">
                            <span>{{ s.final_time }}</span>
                        </td>
                        <td>
                            <el-select
                                v-model="s.staff_id"
                                clearable
                                :disabled="!s.start_time || !s.end_time"
                                placeholder="Selecciona al staff"
                                @change="setItemTimeLine()"
                            >
                                <el-option
                                    v-for="st in filterStaffByService(s.id, s.start_time, s.end_time)"
                                    :key="st.id"
                                    :value="st.id"
                                    :label="`${st.name} ${st.first_name} ${st.last_name}`"
                                    :disabled="isStaffBusy(st.id, s.start_time, s.end_time)"
                                />
                            </el-select>
                        </td>
                    </tr>
                </tbody>
            </table>
        </el-col>
        <el-col :span="24">
            <Timeline
                ref="timeline"
                v-if="staff.length"
                :groups="groups"
                :items="items"
                :viewportMin="new Date(`${form.dateFormatted}T11:00`).getTime()"
                :viewportMax="new Date(`${form.dateFormatted}T20:00`).getTime()"
                :initialViewportStart="initialViewportRange.start"
                :initialViewportEnd="initialViewportRange.end"
                @changeViewport="handleViewportChange"
            >
                <template #item="{ item }">
                    <el-tooltip
                        placement="top"
                        :content="item.itemContent"
                    >
                        <p class="text-white text-center">{{ item.itemContent.length <= 12 ? item.itemContent : item.itemContent.substr(0, 12)+'...' }}</p>
                    </el-tooltip>
                </template>
            </Timeline>
        </el-col>
    </el-row>
    <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="warning" @click="resetForm">Limpiar formulario</el-button>
                <el-button type="primary" @click="saveAppointment">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
    <CreateEditCustomer ref="createEditCustomerRef" />
</template>

<style>
.no-select {
    user-select: none;
}
.disabled-date {
    color: #ccc;
    pointer-events: none; /* no permite click */
}
</style>