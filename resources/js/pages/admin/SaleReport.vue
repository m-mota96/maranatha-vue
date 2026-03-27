<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const { module, menu } = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array,
        required: true
    }
});

const years  = ref([new Date().getFullYear() - 1, new Date().getFullYear()]);
const months = ref([
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
    report_type: 1,
    year: new Date().getFullYear(),
    range: ''
});

const textLabels = (report_type) => {
    switch (report_type) {
        case 6:
            return 'Semestre';
        case 3:
            return 'Trimestre';
        case 1:
            return 'Mes';
        default:
            return 'Ingresa el rango de fechas';
    }
};

const generateSalesReport = async () => {
    const response = await apiClient('admin/reports/sales', 'GET', options.value);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
    window.location.href = window.location.origin+'/reportes/'+response.data;
};
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col :span="24">
            <el-row :gutter="30">
                <el-col :span="24" class="mb-3">
                    <div :span="24" class="bg-purple-500 text-white p-3 rounded-md bold">
                        <font-awesome-icon :icon="['fas', 'info']" /> 
                        &nbsp;En este módulo puedes generar un reporte de ventas en el rango de fecha que mejor
                        se adapte a tus necesidades.
                    </div>
                </el-col>
                <el-col :span="4">
                    <label for="report_type" class="bold">Tipo de reporte</label>
                    <el-select v-model="options.report_type" placeholder="Elige una opción" id="report_type" @change="options.range = ''">
                        <el-option :value="12" label="Anual" />
                        <el-option :value="6" label="Semestral" />
                        <el-option :value="3" label="Trimestral" />
                        <el-option :value="1" label="Mensual" />
                        <el-option :value="0" label="Personalizado" />
                    </el-select>
                </el-col>
                <el-col :span="4" v-if="options.report_type !== 0">
                    <label class="bold">Año</label>
                    <el-select v-if="options.report_type !== 0" v-model="options.year" placeholder="Elige una opción" :filterable="true">
                        <el-option v-for="y in years" :key="y" :value="y" :label="y" />
                    </el-select>
                </el-col>
                <el-col :span="4" v-if="options.report_type !== 12">
                    <label class="bold">{{ textLabels(options.report_type) }}</label>
                    <el-select
                        v-if="options.report_type === 6"
                        v-model="options.range"
                        placeholder="Elige una opción"
                        clearable
                        :filterable="true"
                    >
                        <el-option :value="`${options.year}-01-01,${options.year}-06-30`" label="Enero - Junio" />
                        <el-option :value="`${options.year}-07-01,${options.year}-12-31`" label="Julio - Diciembre" />
                    </el-select>
                    <el-select v-if="options.report_type === 3" v-model="options.range" placeholder="Elige una opción" clearable :filterable="true">
                        <el-option :value="`${options.year}-01-01,${options.year}-03-31`" label="Enero - Marzo" />
                        <el-option :value="`${options.year}-04-30,${options.year}-06-30`" label="Abril - Junio" />
                        <el-option :value="`${options.year}-07-31,${options.year}-09-30`" label="Julio - Septiembre" />
                        <el-option :value="`${options.year}-10-31,${options.year}-12-31`" label="Octubre - Diciembre" />
                    </el-select>
                    <el-select v-if="options.report_type === 1" v-model="options.range" placeholder="Elige una opción" clearable :filterable="true">
                        <el-option v-for="m in months" :key="m.value" :value="m.value" :label="m.label" />
                    </el-select>
                    <el-date-picker
                        v-if="options.report_type === 0"
                        v-model="options.range"
                        class="w-100"
                        type="daterange"
                        range-separator="A"
                        start-placeholder="Fecha inicial"
                        end-placeholder="Fecha final"
                        format="DD/MM/YYYY"
                        value-format="YYYY-MM-DD"
                    />
                    <p v-if="options.report_type === 0" class="mb-0 ps-5 text-center text-sm bold">&nbsp;&nbsp;&nbsp;Rango máximo de 3 meses.</p>
                </el-col>
                <el-col :span="4">
                    <br>
                    <el-button type="success" v-if="options.report_type !== 12" @click="generateSalesReport">
                        <font-awesome-icon class="me-2" :icon="['fas', 'file-excel']" />Generar Excel
                    </el-button>
                    <el-button type="success" v-if="options.report_type === 12" @click="generateSalesReport">
                        <font-awesome-icon class="me-2" :icon="['fas', 'file-csv']" />Generar Csv
                    </el-button>
                </el-col>
            </el-row>
        </el-col>
    </Layout>
</template>

<style scoped>

</style>