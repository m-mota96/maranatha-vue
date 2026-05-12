<script lang="js" setup>
import { ref } from 'vue';
import Layout from './Layout.vue';
import Sales from './reports/Sales.vue';
import StaffCommissions from './reports/StaffCommissions.vue';
import IncomeExpenses from './reports/IncomeExpenses.vue';
import Inventories from './reports/Inventories.vue';

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

const report_type = ref('');
const options     = [
    {
        value: 'staffCommissions',
        label: 'Comisiones de Staff',
    },
    {
        value: 'incomeVsExpenses',
        label: 'Ingresos vs Egresos',
    },
    {
        value: 'inventory',
        label: 'Inventario',
    },
    {
        value: 'sales',
        label: 'Ventas',
    },
];
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col :span="24">
            <el-row :gutter="30">
                <el-col :span="24" class="mb-3">
                    <div :span="24" class="bg-purple-500 text-white p-3 rounded-md bold">
                        <font-awesome-icon :icon="['fas', 'info']" /> 
                        &nbsp;En este módulo puedes generar diferentes reportes que te ayuden a llevar un mejor control y orientación de tu negocio.
                    </div>
                </el-col>
                <el-col :span="6">
                    <label class="bold">Tipo de reporte</label>
                    <el-select v-model="report_type" placeholder="Selecciona una opción" clearable :filterable="true">
                        <el-option
                            v-for="item in options"
                            :key="item.value"
                            :label="item.label"
                            :value="item.value"
                        />
                    </el-select>
                </el-col>
                <Sales v-if="report_type === 'sales'" ref="salesRef" />
                <StaffCommissions v-if="report_type === 'staffCommissions'" ref="staffCommissionsRef" />
                <IncomeExpenses v-if="report_type === 'incomeVsExpenses'" ref="incomeVsExpensesRef" />
                <Inventories v-if="report_type === 'inventory'" ref="inventoriesRef" />
            </el-row>
        </el-col>
    </Layout>
</template>

<style scoped>

</style>