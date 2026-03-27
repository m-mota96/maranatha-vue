<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import { dateEs } from '@/dateEs';
import { format as formatDates } from 'date-fns';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { Chart as highcharts } from 'highcharts-vue';
import { ArrowBigUp, ArrowBigDown, DollarSignIcon } from 'lucide-vue-next';
import { chartForMonth } from '@/chartOptionsForMonth';
import { chartForYear } from '@/chartOptionsForYear';
import { chartPieOptions } from '@/chartPieOptions';

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
const statistics = ref({
    salesCash: 0,
    salesCard: 0,
    salesTransfer: 0,
    totalSalesForMonth: 0,
    totalExpensesForMonth: 0,
});
const search = ref({
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    currentYear: new Date().getFullYear(),
    currentDate: new Date().toLocaleDateString('en-CA')
});
const optionsChartForMonth = ref(chartForMonth());
const optionsChartForYear  = ref(chartForYear());
const optionsChartPie      = ref(chartPieOptions());

onMounted(() => {
    getStatistics();
});

const getStatistics = async (type = 'all') => {
    const response = await apiClient('admin/statistics/all', 'GET', {
        year: search.value.year,
        month: search.value.month,
        currentYear: search.value.currentYear
    });
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    if (type === 'all') {
        statistics.value.salesCash     = response.data.salesCash;
        statistics.value.salesCard     = response.data.salesCard;
        statistics.value.salesTransfer = response.data.salesTransfer;
    }
    if (['all', 'month'].includes(type)) {
        optionsChartForMonth.value.series      = [];
        statistics.value.totalSalesForMonth    = response.data.totalSalesForMonth;
        statistics.value.totalExpensesForMonth = response.data.totalExpensesForMonth;
        createChartForMonth(response.data.salesForMonth, response.data.expensesForMonth);
    }
    if (['all', 'year'].includes(type)) {
        optionsChartForYear.value.series = [];
        createChartForYear(response.data.salesForYear, response.data.expensesForYear);
        optionsChartPie.value.series[0].data = [];
        createChartPie(response.data.totalSalesForYear, response.data.totalExpensesForYear);
    }
};

const createChartForMonth = (sales, expenses)=> {
    const dates = getDaysInMonth(search.value.month, search.value.year);

    optionsChartForMonth.value.xAxis.categories = dates;
    optionsChartForMonth.value.series.push({
        name: 'Ingresos',
        data: Object.values(sales),
        colorByPoint: false,
        color: '#00c951'
    });
    optionsChartForMonth.value.series.push({
        name: 'Egresos',
        data: Object.values(expenses),
        colorByPoint: false,
        color: '#ff6900'
    });
};

const createChartForYear = (sales, expenses) => {
    const monthLabels = months.value.map(month => month.label);

    optionsChartForYear.value.xAxis.categories = monthLabels;
    optionsChartForYear.value.series.push({
        name: 'Ingresos',
        data: Object.values(sales),
        colorByPoint: false,
        color: '#00c951'
    });
    optionsChartForYear.value.series.push({
        name: 'Egresos',
        data: Object.values(expenses),
        colorByPoint: false,
        color: '#ff6900'
    });
};

const createChartPie = (sales, expenses)=> {
    const balance = sales - expenses;
    const color   = balance < 0 ? '#fb2c36' : '#2b7fff';
    optionsChartPie.value.series[0].data = [
        { name: 'Ingresos', y: sales, color: '#00c951' },
        { name: 'Egresos', y: expenses, color: '#ff6900' },
        { name: 'Balance', y: Math.abs(balance), realValue: balance, color, sliced: true, selected: true }
    ];
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const getDaysInMonth = (month, year)=> {
    const days = [];
    const totalDays = new Date(year, month, 0).getDate();

    for (let day = 1; day <= totalDays; day++) {
        days.push(day);
    }

    return days;
};
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col :span="24"class="ps-4 pe-4">
            <el-row :gutter="30">
                <el-col :span="24" class="my-card text-center mb-3 p-4">
                    <p class="text-black bold text-2xl">{{ dateEs(search.currentDate, ' de ', 0, true) }}</p>
                    <el-row>
                        <el-col :span="6">
                            <p class="text-black mb-1">Ingresos en efectivo</p>
                            <p class="text-gray-700 bold text-3xl">{{ formatCurrency(statistics.salesCash) }}</p>
                        </el-col>
                        <el-col :span="6">
                            <p class="text-black mb-1">Ingresos con tarjeta</p>
                            <p class="text-gray-700 bold text-3xl">{{ formatCurrency(statistics.salesCard) }}</p>
                        </el-col>
                        <el-col :span="6">
                            <p class="text-black mb-1">Ingresos con transferencia</p>
                            <p class="text-gray-700 bold text-3xl">{{ formatCurrency(statistics.salesTransfer) }}</p>
                        </el-col>
                        <el-col :span="6">
                            <p class="text-black mb-1">Total de ingresos</p>
                            <p class="text-gray-700 bold text-3xl">{{ formatCurrency(statistics.salesCash + statistics.salesCard + statistics.salesTransfer) }}</p>
                        </el-col>
                    </el-row>
                </el-col>
                <el-divider />
                <el-col :span="18" class="my-card text-center mb-3 mt-3 p-4">
                    <el-form-item>
                        <template #label>
                            <span class="text-xl bold text-black">Resumen mensual</span>
                        </template>
                        <el-select v-model="search.month" class="w-20" @change="getStatistics('month')">
                            <el-option v-for="m in months" :key="m.value" :value="m.value" :label="m.label" />
                        </el-select>
                        <el-select v-model="search.year" class="w-15 ml-3" @change="getStatistics('month')">
                            <el-option v-for="y in years" :key="y" :value="y" :label="y" />
                        </el-select>
                    </el-form-item>
                    <highcharts
                        :options="optionsChartForMonth"
                        class="chart-full-height mt-4"
                    />
                </el-col>
                <el-col :span="6" class="text-center mb-3 mt-3 ps-4 pe-0 pt-0 pb-0">
                    <el-col :span="24" class="my-card height-card p-4 mb-3 pr">
                        <p class="text-black bold text-xl mb-3">Ingresos</p>
                        <p class="text-green-500 bold text-3xl mb-3">{{ formatCurrency(statistics.totalSalesForMonth) }}</p>
                        <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                        <ArrowBigUp class="inline-block pa text-green-500" :size="45" style="top: 60px; right: 25px;" />
                    </el-col>
                    <el-col :span="24" class="my-card height-card p-4 mb-3 pr">
                        <p class="text-black bold text-xl mb-3">Egresos</p>
                        <p class="text-orange-500 bold text-3xl mb-3">{{ formatCurrency(statistics.totalExpensesForMonth) }}</p>
                        <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                        <ArrowBigDown class="inline-block pa text-orange-500" :size="45" style="top: 60px; right: 25px;" />
                    </el-col>
                    <el-col :span="24" class="my-card height-card p-4 pr">
                        <p class="text-black bold text-xl mb-3">Balance</p>
                        <p
                            :class="((statistics.totalSalesForMonth - statistics.totalExpensesForMonth) < 0) ? 'text-red-500' : 'text-blue-500'"
                            class="bold text-3xl mb-3"
                        >
                            {{ formatCurrency(statistics.totalSalesForMonth - statistics.totalExpensesForMonth) }}
                        </p>
                        <p class="text-black">{{ months[search.month - 1].label }} de {{ search.year }}</p>
                        <DollarSignIcon
                            :class="((statistics.totalSalesForMonth - statistics.totalExpensesForMonth) < 0) ? 'text-red-500' : 'text-blue-500'"
                            class="inline-block pa"
                            :size="35"
                            style="top: 68px; right: 25px;"
                        />
                    </el-col>
                </el-col>
                <el-divider />
                <el-col :span="16" class="my-card text-center mb-3 mt-3 p-4">
                    <el-form-item>
                        <template #label>
                            <span class="text-xl bold text-black">Resumen anual</span>
                        </template>
                        <el-select v-model="search.currentYear" class="w-15 ml-3" @change="getStatistics('year')">
                            <el-option v-for="y in years" :key="y" :value="y" :label="y" />
                        </el-select>
                    </el-form-item>
                    <highcharts
                        :options="optionsChartForYear"
                        class="chart-full-height mt-4"
                    />
                </el-col>
                <el-col :span="8" class="mb-3 mt-3 ps-4">
                    <el-col :span="24" class="my-card text-center pt-4" style="height: 525px;">
                        <highcharts
                            :options="optionsChartPie"
                            class="chart-full-height mt-4"
                        />
                    </el-col>
                </el-col>
            </el-row>
        </el-col>
    </Layout>
</template>

<style scoped>
.my-card {
    border-width: 5px 1px 1px;
    border-style: solid;
    border-color: rgb(6, 120, 183);
    border-image: initial;
    border-radius: 1rem;
}

.height-card {
    height: 168px;
}
</style>