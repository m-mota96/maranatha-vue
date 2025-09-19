<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose, onMounted } from 'vue';

const dialogVisible = ref(false);
const appointment   = ref({});

const showModal = (appointmentId) => {
    getAppointment(appointmentId);
    dialogVisible.value = true;
};

const getAppointment = async (appointmentId) => {
    const response = await apiClient(`admin/appointment/${appointmentId}`);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    appointment.value = response.data;
};

const time = (time)=> {
    let hour = parseInt(time.substring(0, 2));
    const txt = (hour >= 12) ? 'PM' : 'AM';
    hour = (hour > 12) ? (hour - 12) : hour;
    hour = (hour < 10 && parseInt(hour) !== 0) ? '0'+hour : hour;
    return hour+time.substring(2, 5)+' '+txt;
};

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
        title="Registrar venta"
        width="900"
        style="margin-top: 1% !important;"
    >
        <el-row :gutter="10">
            <el-col :span="24">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <!-- <th>Hora de inicio</th>
                            <th>Hora final</th> -->
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="s in appointment.services">
                            <td>{{ s.name }}</td>
                            <!-- <td>{{ time(s.pivot.start_time) }}</td>
                            <td>{{ time(s.pivot.end_time) }}</td> -->
                            <td class="text-center">1</td>
                            <td class="text-center">{{ formatCurrency(s.pivot.price) }}</td>
                        </tr>
                    </tbody>
                </table>
            </el-col>
        </el-row>
    </el-dialog>
</template>

<style scoped>

</style>