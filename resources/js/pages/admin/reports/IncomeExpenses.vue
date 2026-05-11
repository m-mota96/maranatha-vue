<script lang="js" setup>
import { ref } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const loading = ref(false);
const years   = ref([new Date().getFullYear() - 1, new Date().getFullYear()]);
const months  = ref([
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
    daily_report: false
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

const generateIncomeVsExpensesReport = async () => {
    loading.value  = true;
    const response = await apiClient('admin/reports/incomeVsExpenses', 'GET', options.value);
    loading.value  = false;
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
    window.location.href = window.location.origin+'/reportes/'+response.data;
};

const handleChange = (_period) => {
    options.value.range  = '';
    const notDailyReport = [6, 12];
    if (notDailyReport.includes(_period)) {
        options.value.daily_report = false;
    }
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
            placeholder="Elige una opción"
            clearable
            :filterable="true"
        >
            <el-option :value="`${options.year}-01-01,${options.year}-06-30`" label="Enero - Junio" />
            <el-option :value="`${options.year}-07-01,${options.year}-12-31`" label="Julio - Diciembre" />
        </el-select>
        <el-select v-if="options.period === 3" v-model="options.range" placeholder="Elige una opción" clearable :filterable="true">
            <el-option :value="`${options.year}-01-01,${options.year}-03-31`" label="Enero - Marzo" />
            <el-option :value="`${options.year}-04-30,${options.year}-06-30`" label="Abril - Junio" />
            <el-option :value="`${options.year}-07-31,${options.year}-09-30`" label="Julio - Septiembre" />
            <el-option :value="`${options.year}-10-31,${options.year}-12-31`" label="Octubre - Diciembre" />
        </el-select>
        <el-select v-if="options.period === 1" v-model="options.range" placeholder="Elige una opción" clearable :filterable="true">
            <el-option v-for="m in months" :key="m.value" :value="m.value" :label="m.label" />
        </el-select>
        <el-date-picker
            v-if="options.period === 0"
            v-model="options.range"
            class="w-100"
            type="daterange"
            range-separator="A"
            start-placeholder="Fecha inicial"
            end-placeholder="Fecha final"
            format="DD/MM/YYYY"
            value-format="YYYY-MM-DD"
        />
        <p v-if="options.period === 0" class="mb-0 text-center text-sm bold">&nbsp;&nbsp;&nbsp;Rango máximo de 3 meses.</p>
    </el-col>
    <el-col :span="6" class="mb-3" v-if="options.period !== 12 && options.period !== 6">
        <label class="bold">¿Desglosar reporte por día?</label><br>
        <el-radio-group v-model="options.daily_report">
            <el-radio :value="false">No</el-radio>
            <el-radio :value="true">Si</el-radio>
        </el-radio-group>
    </el-col>
    <el-col :span="6" class="mb-3">
        <br>
        <el-button type="success" @click="generateIncomeVsExpensesReport" :loading="loading">
            <font-awesome-icon class="me-2" :icon="['fas', 'file-excel']" />Generar reporte
        </el-button>
    </el-col>
</template>