<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { addMinutesToTime } from '@/addMinutesToTime';
import { ref, defineExpose, onMounted } from 'vue';
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
const form                  = ref({
    customer_id: null,
    customer: '',
    date: new Date(),
    dateFormatted: '',
    horary: '',
    service_type: [],
    services: []
});
const groups = [
    { id: 1, label: 'Usuario 1' },
    { id: 2, label: 'Usuario 2' },
  ];

const items = [
    { group: 1, type: 'range', start: new Date(`2025-08-01T06:00:00`).getTime(), end: new Date(`2025-08-01T07:00:00`).getTime(), itemContent: 'Pedicure' },
    { group: 2, type: 'range', start: new Date(`2025-08-01T12:00:00`).getTime(), end: new Date(`2025-08-01T15:00:00`).getTime(), itemContent: 'Manicure' },
];

const showModal = () => {
    resetForm();
    for (let i = 0; i < serviceType.length; i++) {
        form.value.service_type.push('');
        form.value.services.push('');
    }
    dialogVisible.value = true;
};

const searchStaff = async () => {
    previewServices();
    form.value.dateFormatted = formatDate(form.value.date);
    const response           = await apiClient('admin/searchStaff', 'GET', { dateFormatted: form.value.dateFormatted, horary: form.value.horary });
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    staff.value = response.data;
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
    form.value.horary        = '';
    form.value.service_type  = [];
    form.value.services      = [];
    quantityServices.value   = 1;
    services.value           = [];
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

const moreMinus = (value, index, currentIndex) => {
    let val          = form.value.services[index][currentIndex].quantity;
    val              = val + (value);
    if (val < 1) val = 1;
    form.value.services[index][currentIndex].quantity = val;
    previewServices();
};

const previewServices = () => {
    services.value = [];
    // let initialHorary = to24HourFormat(form.value.horary);
    let initialHorary = form.value.horary ? form.value.horary : '';
    let time          = null;
    form.value.services.forEach(s => {
        for (let i = 0; i < s.length; i++) {
            if (s[i].active) {
                for (let j = 0; j < s[i].quantity; j++) {
                    services.value.push({
                        uid: generateUID(),
                        id: s[i].id,
                        name: s[i].name+` (${s[i].time} min.)`,
                        time: s[i].time,
                        staff_id: '',
                        initial_time: initialHorary,
                        final_time: initialHorary ? addMinutesToTime(initialHorary, s[i].time) : '--:--',
                        start_time: initialHorary ? to24HourFormat(initialHorary) : null,
                        end_time: initialHorary ? to24HourFormat(addMinutesToTime(initialHorary, s[i].time)) : null
                    });
                    time = s[i].time;
                }
                if (time && initialHorary) {
                    initialHorary = addMinutesToTime(initialHorary.toString(), time);
                }
            }
        }
    });
};

const filterStaffByService = (service_id) => {
    return staff.value.filter(person => 
        person.services.some(service => service.id === service_id)
    );
};

const isStaffBusy = (staffId, initial_time, final_time) => {
    return services.value.some(s =>
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
    const [time, modifier] = time12h.split(' '); // "10:00", "PM"
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
                    v-model="form.customer"
                    :fetch-suggestions="querySearchAsync"
                    placeholder="Escribe para buscar un cliente"
                    @select="handleSelect"
                    :trigger-on-focus="false"
                    :debounce="300"
                    clearable
                />
            </el-col>
            <el-col :span="24">
                <p class="bold text-black mb-0">Fecha de cita</p>
                <el-calendar class="w-100" v-model="form.date" />
            </el-col>
        </el-col>
        <el-col :span="6">
            <el-col :span="24" class="mb-3">
                <p class="bold text-black mb-0">Hora de cita</p>
                <el-time-select
                    v-model="form.horary"
                    start="07:00"
                    step="00:10"
                    end="23:59"
                    placeholder="Selecciona la hora"
                    format="hh:mm A"
                    @change="searchStaff()"
                />
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
                                        @change="previewServices()"
                                    >
                                        <span style="white-space: normal;">
                                            {{ s.name }} ({{ formatCurrency(s.price) }} - {{ s.time }} min.)
                                        </span>
                                    </el-checkbox>
                                </td>
                                <td class="p-o text-center" style="width: 20%;">
                                    <span v-if="s.active">
                                        <font-awesome-icon class="my-btn me-2" :icon="['fas', 'minus']" @click="moreMinus(-1, (i - 1), j)" />
                                        <span class="bold no-select">{{ s.quantity }}</span>
                                        <font-awesome-icon class="my-btn ms-2" :icon="['fas', 'plus']" @click="moreMinus(1, (i - 1), j)" />
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
                                start="07:00"
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
                v-if="dialogVisible"
                :groups="groups"
                :items="items"
                :viewportMin="new Date(`2025-08-01T06:00:00`).getTime()"
                :viewportMax="new Date(`2025-08-01T23:00:00`).getTime()"
            />
        </el-col>
    </el-row>
    <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
    <CreateEditCustomer ref="createEditCustomerRef" />
</template>

<style scoped>
.no-select {
    user-select: none;
}
</style>