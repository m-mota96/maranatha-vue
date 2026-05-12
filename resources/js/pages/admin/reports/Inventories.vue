<script lang="js" setup>
import { ref } from 'vue';
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
    canceled_sales: false
});
const errors = ref({
    range: false,
    range_invalid: false,
});

const textLabels = (period) => {
    switch (period) {
        case 6:
            return 'Semestre';
        case 3:
            return 'Trimestre';
        case 1:
            return 'Mes';
        default:
            return 'Rango de fechas';
    }
};

const generateInventoriesReport = async () => {
    if (validate()) {
        loading.value   = true;
        txtButton.value = 'Generando reporte, por favor espera!!';
        const response  = await apiClient('admin/reports/inventories', 'GET', options.value);
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

const handleChange = (_period) => {
    options.value.range        = '';
    errors.value.range         = false;
    errors.value.range_invalid = false;
};

const validate = () => {
    errors.value.range         = false;
    errors.value.range_invalid = false;
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
            <el-option :value="12" label="Anual" />
            <el-option :value="6" label="Semestral" />
            <el-option :value="3" label="Trimestral" />
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
            v-if="options.period === 6"
            v-model="options.range"
            class="el-form-item"
            :class="{'is-error': errors.range}"
            placeholder="Elige una opción"
            clearable
            :filterable="true"
        >
            <el-option :value="`${options.year}-01-01,${options.year}-06-30`" label="Enero - Junio" />
            <el-option :value="`${options.year}-07-01,${options.year}-12-31`" label="Julio - Diciembre" />
        </el-select>
        <el-select
            v-if="options.period === 3"
            v-model="options.range"
            class="el-form-item"
            :class="{'is-error': errors.range}"
            placeholder="Elige una opción"
            clearable
            :filterable="true"
        >
            <el-option :value="`${options.year}-01-01,${options.year}-03-31`" label="Enero - Marzo" />
            <el-option :value="`${options.year}-04-30,${options.year}-06-30`" label="Abril - Junio" />
            <el-option :value="`${options.year}-07-31,${options.year}-09-30`" label="Julio - Septiembre" />
            <el-option :value="`${options.year}-10-31,${options.year}-12-31`" label="Octubre - Diciembre" />
        </el-select>
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
            :class="{'is-error': errors.range || errors.range_invalid}"
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
    <el-col :span="6" class="mb-3">
        <br>
        <el-button type="success" @click="generateInventoriesReport" :loading="loading">
            <font-awesome-icon class="me-2" :icon="['fas', 'file-excel']" />{{ txtButton }}
        </el-button>
    </el-col>
</template>