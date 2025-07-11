<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentStaff } = defineProps({
    getParentStaff: Function
});

const dialogVisible   = ref(false);
const staff_schedules = ref([]);
const staff_name      = ref('');
const method          = ref('PUT');
const week            = ref([
    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
]);
const schedule = ref([{
    start_time: '',
    meal_start_time: '',
    meal_end_time: '',
    end_time: ''
}]);

const saveSchedule = async () => {
    if (validate()) {

    }
}

const validate = () => {
    let valid     = true;
    let dayActive = false;
    staff_schedules.value.forEach(s => {
        if (s.status) {
            dayActive = true;
        }
    });
    if (!dayActive) {
        showNotification('Debes marcar al menos 1 día de trabajo.', '¡Error!', 'error', 8000);
        valid = false;
    }
    return valid;
}

const showModal = (_id, _staff_name, _staff_schedules) => {
    staff_name.value      = _staff_name;
    staff_schedules.value = _staff_schedules;
    if (!staff_schedules.value.length) {
        method.value = 'POST';
        for (let i = 0; i < 7; i++) {
            staff_schedules.value.push({
                staff_id: _id,
                day: (i + 1),
                start_time: '',
                end_time: '',
                meal_start_time: '',
                meal_end_time: '',
                status: false,
            });
        }
    }
    dialogVisible.value = true;
};

const setValue = (val, index) => {
    staff_schedules.value.forEach(s => {
        s[index] = val;
    });
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="`Horario de ${staff_name}`"
        width="1200"
        style="margin-top: 2% !important;"
    >
        <el-table
            :data="schedule"
            stripe
            empty-text="Ningún dato disponible en esta tabla"
            header-cell-class-name="text-dark bold"
            row-class-name="text-dark"
            class="mt-4"
            v-if="method === 'POST'"
        >
            <el-table-column label="" width="150" align="center">
                <template #default="scope">
                    -----
                </template>
            </el-table-column>
            <el-table-column label="Hora de entrada">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="schedule[scope.$index].start_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                        @change="(val) => setValue(val, 'start_time')"
                    />
                </template>
            </el-table-column>
            <el-table-column label="Hora de salida a comer">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="schedule[scope.$index].meal_start_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                        @change="(val) => setValue(val, 'meal_start_time')"
                    />
                </template>
            </el-table-column>
            <el-table-column label="Hora de entrada de comer">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="schedule[scope.$index].meal_end_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                        @change="(val) => setValue(val, 'meal_end_time')"
                    />
                </template>
            </el-table-column>
            <el-table-column label="Hora de salida">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="schedule[scope.$index].end_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                        @change="(val) => setValue(val, 'end_time')"
                    />
                </template>
            </el-table-column>
            <el-table-column align="center" width="150">
                -----
            </el-table-column>
        </el-table>
        <p class="mt-1 mb-5 text-dark" v-if="method === 'POST'">
            <b class="text-danger">Nota: </b>
            Puedes autocompletar los horarios en la tabla de arriba para facilitar el 
            llenado y si necesitas modificar alguno en específico lo puedes hacer.
        </p>
        <el-table
            :data="staff_schedules"
            stripe
            empty-text="Ningún dato disponible en esta tabla"
            header-cell-class-name="text-dark bold"
            row-class-name="text-success"
            class="mb-4"
        >
            <el-table-column label="Día de la semana" width="150" align="center">
                <template #default="scope">
                    <b>{{ week[(scope.row.day - 1)] }}</b>
                </template>
            </el-table-column>
            <el-table-column label="Hora de entrada">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="staff_schedules[scope.$index].start_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                    />
                </template>
            </el-table-column>
            <el-table-column label="Hora de salida a comer">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="staff_schedules[scope.$index].meal_start_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                    />
                </template>
            </el-table-column>
            <el-table-column label="Hora de entrada de comer">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="staff_schedules[scope.$index].meal_end_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                    />
                </template>
            </el-table-column>
            <el-table-column label="Hora de salida">
                <template #default="scope">
                    <el-time-picker
                        class="w-100"
                        v-model="staff_schedules[scope.$index].end_time"
                        placeholder="Elige la hora"
                        :is-show-seconds="false"
                        format="HH:mm"
                        value-format="HH:mm"
                        clearable
                    />
                </template>
            </el-table-column>
            <el-table-column label="¿Trabaja este día?" align="center" width="150">
                <template #default="scope">
                    <el-checkbox v-model="staff_schedules[scope.$index].status" size="large" />
                </template>
            </el-table-column>
        </el-table>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveSchedule">Guardar cambios</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>