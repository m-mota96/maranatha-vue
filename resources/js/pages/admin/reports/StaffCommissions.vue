<script lang="js" setup>
import { ref, onMounted, watch } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const loading   = ref(false);
const txtButton = ref('Generar reporte');
const years     = ref([new Date().getFullYear() - 1, new Date().getFullYear()]);
const months    = ref([
    { value: 1, label: 'Enero' },
    { value: 2, label: 'Febrero' },
    { value: 3, label: 'Marzo' },
    { value: 4, label: 'Abril' },
    { value: 5, label: 'Mayo' },
    { value: 6, label: 'Junio' },
    { value: 7, label: 'Julio' },
    { value: 8, label: 'Agosto' },
    { value: 9, label: 'Septiembre' },
    { value: 10, label: 'Octubre' },
    { value: 11, label: 'Noviembre' },
    { value: 12, label: 'Diciembre' },
]);
const options = ref({
    period: 1,
    year: new Date().getFullYear(),
    range: new Date().getMonth() + 1,
    staff: []
});
const errors = ref({
    range: false,
    range_invalid: false,
    staff: false,
});
const staff         = ref([]);
const checkAll      = ref(false);
const indeterminate = ref(false);

onMounted(() => {
    getStaff();
});

const textLabels = (period) => {
    switch (period) {
        case 1:
            return 'Mes';
        default:
            return 'Rango de fechas';
    }
};

const getStaff = async () => {
    const response = await apiClient('admin/allStaff');
    if (response.error) {
        showNotification(reponse.msj, '¡Error!', 'error');
        return
    }
    // staff.value = response.data;
    staff.value = response.data.map(item => ({
        value: item.id,
        label: `${item.first_name} ${item.last_name} ${item.name}`
    }));
    // staff.value.unshift({ label: 'Seleccionar todos', value: '__all__' });
};

const generateStaffCommissionsReport = async () => {
    if (validate()) {
        loading.value   = true;
        txtButton.value = 'Generando reporte, por favor espera!!';
        const response  = await apiClient('admin/reports/staffCommissions', 'GET', options.value);
        loading.value   = false;
        txtButton.value = 'Generar reporte';
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error');
            return
        }
        showNotification(response.msj);
        window.location.href = window.location.origin+'/reportes/'+response.data;
    }
};

const handleCheckAll = (val) => {
    indeterminate.value = false
    if (val) {
        options.value.staff = staff.value.map((_) => _.value);
    } else {
        options.value.staff = [];
    }
};

watch(
    ()    => options.value.staff,
    (val) => {
        if (val.length === 0) {
            checkAll.value      = false;
            indeterminate.value = false;
        } else if (val.length === staff.value.length) {
            checkAll.value      = true;
            indeterminate.value = false;
        } else {
            indeterminate.value = true;
        }
    }
);

const handleChange = (_period) => {
    options.value.range        = '';
    errors.value.range         = false;
    errors.value.range_invalid = false;
    errors.value.staff         = false;
};

const validate = () => {
    errors.value.range         = false;
    errors.value.range_invalid = false;
    errors.value.staff         = false;
    let valid = true;
    if (!options.value.range) {
        errors.value.range = true;
        valid = false;
    }
    if (options.value.period === 0 && options.value.range) {
        const diference = diferenceMonths(new Date(options.value.range[0]), new Date(options.value.range[1]));
        if (diference > 3) {
            errors.value.range_invalid = true;
            valid = false;
        }
    }
    if (!options.value.staff.length) {
        errors.value.staff = true;
        valid = false;
    }
    return valid;
};

const diferenceMonths = (startDate, endDate) => {
    const y = endDate.getFullYear() - startDate.getFullYear();
    const m = endDate.getMonth() - startDate.getMonth();

    return (y * 12) + m;
};
</script>

<template>
    <el-col :span="6" class="mb-3">
        <label for="period" class="bold">Periodo</label>
        <el-select v-model="options.period" placeholder="Elige una opción" id="period" @change="(val) => handleChange(val)">
            <!-- <el-option :value="12" label="Anual" /> -->
            <el-option :value="1" label="Mensual" />
            <el-option :value="0" label="Personalizado" />
        </el-select>
    </el-col>
    <el-col :span="6" class="mb-3" v-if="options.period !== 0">
        <label class="bold">Año</label>
        <el-select v-if="options.period !== 0" v-model="options.year" placeholder="Elige una opción" :filterable="true">
            <el-option v-for="y in years" :key="y" :value="y" :label="y" />
        </el-select>
    </el-col>
    <el-col :span="6" class="mb-3" v-if="options.period !== 12">
        <label class="bold">{{ textLabels(options.period) }}</label>
        <el-select
            v-if="options.period === 1"
            v-model="options.range"
            class="el-form-item"
            :class="{'is-error': errors.range}"
            placeholder="Elige una opción"
            clearable
            :filterable="true"
        >
            <el-option v-for="m in months" :key="m.value" :value="m.value" :label="m.label" />
        </el-select>
        <el-date-picker
            v-if="options.period === 0"
            v-model="options.range"
            class="el-form-item w-100"
            :class="{'is-error': errors.range}"
            type="daterange"
            range-separator="A"
            start-placeholder="Fecha inicial"
            end-placeholder="Fecha final"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
        />
        <p v-if="options.period === 0" class="mb-0 text-center text-sm bold">&nbsp;&nbsp;&nbsp;Rango máximo de 3 meses.</p>
        <span class="text-danger fs-small" v-if="errors.range">Elige el {{ textLabels(options.period).toLocaleLowerCase() }}.</span>
        <span class="text-danger fs-small" v-if="errors.range_invalid">Rango excedido.</span>
    </el-col>
    <el-col :span="6">
        <label class="bold">¿De que persona/s quieres generar el reporte?</label>
        <el-select-v2
            v-model="options.staff"
            class="el-form-item"
            :class="{'is-error': errors.staff}"
            :options="staff"
            multiple
            clearable
            collapse-tags
            placeholder="Eliga al staff"
            popper-class="custom-header"
            :max-collapse-tags="1"
        >
            <template #header>
            <el-checkbox
                v-model="checkAll"
                :indeterminate="indeterminate"
                @change="handleCheckAll"
            >
                Seleccionar todo
            </el-checkbox>
            </template>
        </el-select-v2>
        <span class="text-danger fs-small" v-if="errors.staff">Elige al menos una persona.</span>
    </el-col>
    <el-col :span="6" class="mb-3">
        <br>
        <el-button type="success" @click="generateStaffCommissionsReport" :loading="loading">
            <font-awesome-icon class="me-2" :icon="['fas', 'file-excel']" />{{ txtButton }}
        </el-button>
    </el-col>
</template>