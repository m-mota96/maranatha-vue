<script lang="js" setup>
import { ref, defineExpose } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const dialogVisible = ref(false);
const sale          = ref({});

const showModal = async (id) => {
    const response = await apiClient('admin/sale', 'GET', {id});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    sale.value = response.data;
    dialogVisible.value = true;
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        title="Resumen de venta"
        width="50%"
        style="margin-top: 2% !important;"
    >
        <table class="w-100">
            <thead>
                <tr>
                    <th class="!bg-blue-100 text-dark bold">Servicio/Producto</th>
                    <th class="!bg-blue-100 text-dark bold text-center">Precio</th>
                    <th class="!bg-blue-100 text-dark bold text-center">Cantidad</th>
                    <th class="!bg-blue-100 text-dark bold text-center">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="s in sale.appointment.services" :key="s.id">
                    <td>
                        {{ s.name }}
                    </td>
                    <td class="text-center">
                        {{ formatCurrency(s.pivot.price) }}
                    </td>
                    <td class="text-center">
                        1
                    </td>
                    <td class="text-center">
                        {{ formatCurrency(s.pivot.price) }}
                    </td>
                </tr>
                <tr v-for="i in sale.inventories" :key="i.id">
                    <td>
                        <span v-if="i.product.brand"> {{ i.product.brand }}</span>
                        {{ i.product.name }}
                        <span v-if="i.product.content">{{ i.product.content }} {{ i.product.abreviation }}</span>
                    </td>
                    <td class="text-center">
                        {{ formatCurrency(i.price) }}
                    </td>
                    <td class="text-center">
                        {{ i.product.type_sale === 'pza' ? parseInt(i.quantity) : i.quantity }}
                    </td>
                    <td class="text-center">
                        {{ formatCurrency(i.price * i.quantity) }}
                    </td>
                </tr>
            </tbody>
        </table>
        <el-col :span="8" class="pr">
            <h4 class="mt-2 text-dark normal">Subtotal: <span style="position: absolute; right: 0;">{{ formatCurrency(sale.subtotal) }}</span></h4>
            <h4 class="mt-2 text-dark normal">
                Descuento: 
                <span v-if="sale.discount" style="position: absolute; right: 0;">{{ sale.type_discount === 'amount' ? formatCurrency(sale.discount) : sale.discount + '%' }}</span>
                <span v-if="!sale.discount" style="position: absolute; right: 0;">N/A</span>
            </h4>
            <h4 class="mt-2 text-dark normaboldl">Total: <span style="position: absolute; right: 0;">{{ formatCurrency(sale.total) }}</span></h4>
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cerrar</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
table {
    border: 1px solid;
    border-radius: 5px;
    border-collapse: separate; /* Obligatorio para que funcione el radius */
    border-spacing: 0; 
    overflow: hidden;
}
table thead th {
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 5px;
    padding-bottom: 5px;
}
table tbody td {
    padding-left: 10px;
    padding-right: 10px;
    padding-top: 5px;
    padding-bottom: 5px;
}
</style>