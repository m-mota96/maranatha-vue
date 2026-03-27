<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentStaff } = defineProps({
    getParentStaff: Function
});

const dialogVisible   = ref(false);
const staff_id        = ref('');
const staff_name      = ref('');
const method          = ref('POST');
const week            = ref([
    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
]);
const schedule = ref({
    workDay: [],
    day: [],
    schedule: [],
});
const errors= ref([]);

const saveSchedule = async () => {
    if (validate()) {
        schedule.value.workDay.forEach(s => {
            const day = week.value.findIndex(w => w === s);
            schedule.value.day.push(day);
        });
        const response = await apiClient('admin/schedule', method.value, {id: staff_id.value, schedule: schedule.value.schedule, days: schedule.value.day});
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        getParentStaff();
        showNotification(response.msj);
    }
}

const validate = () => {
    let valid = true;
    if (!schedule.value.workDay.length) {
        showNotification('Debes elegir al menos un día de jornada laboral.', '¡Error!', 'error');
        valid = false;
    } else {
        if (areSchedulesValid(schedule.value.schedule)) {
            showNotification('Algunos horarios no son consecutivos<br>o están incompletos.', '¡Error!', 'error', 8000);
            valid = false;
        }
    }
    return valid;
}

const showModal = async (_id, _staff_name) => {
    resetForm();
    staff_id.value   = _id;
    staff_name.value = _staff_name;
    const response   = await apiClient('admin/schedules', 'GET', { id: _id });
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    if (response.data.length) {
        method.value = 'PUT';
        response.data.forEach(s => {
            schedule.value.workDay.push(week.value[s.day]);
            schedule.value.schedule.push({
                day: week.value[s.day],
                start_time: to12HourFormat(s.start_time),
                start_break: s.meal_start_time ? to12HourFormat(s.meal_start_time) : '',
                end_break: s.meal_end_time ? to12HourFormat(s.meal_end_time) : '',
                end_time: to12HourFormat(s.end_time),
            });
        });
    }
    dialogVisible.value = true;
};

const setDay = (checked, day) => {
    errors.value = [];
    if (checked) {
        schedule.value.schedule.push({
            day,
            start_time: '',
            start_break: '',
            end_break: '',
            end_time: ''
        });
    } else {
        const index = schedule.value.schedule.findIndex(s => s.day === day);
        if (index !== -1) {
            schedule.value.schedule.splice(index, 1);
        }
    }

    schedule.value.workDay.sort((a, b) => week.value.indexOf(a) - week.value.indexOf(b));

    schedule.value.schedule.sort((a, b) => {
        return week.value.indexOf(a.day) - week.value.indexOf(b.day);
    });
};

const resetForm = () => {
    errors.value            = [];
    method.value            = 'POST';
    schedule.value.workDay  = [];
    schedule.value.day      = [];
    schedule.value.schedule = [];
};

const areSchedulesValid = (schedules) => {
    let error    = false;
    errors.value = [];
    schedules.forEach((schedule, index) => {
        let isValid       = true;
        const start_time  = schedule.start_time ? to24HourFormat(schedule.start_time) : null;
        const start_break = schedule.start_break ? to24HourFormat(schedule.start_break) : null;
        const end_break   = schedule.end_break ? to24HourFormat(schedule.end_break) : null;
        const end_time    = schedule.end_time ? to24HourFormat(schedule.end_time) : null;
        
        // Nos aseguramos que todos los campos estén llenos
        // if (!start_time || !start_break || !end_break || !end_time) {
        if (!start_time || !end_time) {
            // errors.value[index] = true;
            isValid = false;
        };

        // Convertimos a minutos para comparar
        const toMinutes = (timeStr) => {
            const [h, m] = timeStr.split(':').map(Number);
            return h * 60 + m;
        };

        const start     = start_time ? toMinutes(start_time) : null;
        const mealStart = start_break ? toMinutes(start_break) : null;
        const mealEnd   = end_break ? toMinutes(end_break) : null;
        const end       = end_time ? toMinutes(end_time) : null;
        
        if (mealStart && mealEnd) {
            isValid = start < mealStart && mealStart < mealEnd && mealEnd < end;
        } else if (start && end) {
            isValid = start < end;
        } else {
            isValid = false;
        }

        if (!isValid) {
            error               = true;
            errors.value[index] = true;
        }
    });
    return error;
};

const to12HourFormat = (horary) => {
    // Divido la cadena en horas y minutos
    let [hours, minutes] = horary.split(':');

    // Determino si es AM o PM
    const suffix = hours >= 12 ? 'PM' : 'AM';

    // Convierto la hora al formato de 12 horas (el resto de 12)
    // El caso de '00' se maneja convirtiéndolo a 12
    hours = (hours % 12) || 12;

    // Aseguro que las horas siempre tengan dos dígitos (ej. '05')
    const horasFormateadas = hours.toString().padStart(2, '0');

    return `${horasFormateadas}:${minutes} ${suffix}`;
};

const to24HourFormat = (time12h) => {
    const [time, modifier] = time12h.split(' ');
    let [hours, minutes] = time.split(':').map(Number);

    if (modifier === 'PM' && hours < 12) hours += 12;
    if (modifier === 'AM' && hours === 12) hours = 0;

    return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="`Horario de ${staff_name}`"
        width="90%"
        style="margin-top: 2% !important;"
    >
        <label class="bold">Jornada <span class="text-danger">*</span></label>
        <el-checkbox-group v-model="schedule.workDay">
            <el-checkbox-button v-for="w in week" :key="w" :value="w" @change="(val) => setDay(val, w)">
                {{ w }}
            </el-checkbox-button>
        </el-checkbox-group>
        <label class="bold mt-3">Horario <span class="text-danger">*</span></label>
        <el-row :gutter="20" class="mb-5">
            <el-col :span="4" v-for="(s, i) in schedule.schedule" :key="i">
                <el-card class="mb-4">
                    <p class="text-base text-dark text-center">{{ schedule.workDay[i] }}</p>
                    <el-divider />
                    <p class="!text-purple-600 mb-0">Entrada <span class="text-danger">*</span></p>
                    <el-time-select
                        v-model="s.start_time"
                        class="el-form-item w-100"
                        :class="{'is-error': false}"
                        start="08:00"
                        step="00:5"
                        end="20:00"
                        placeholder="Hora"
                        format="hh:mm A"
                        clearable
                    />
                    <p class="!text-purple-600 mb-0 mt-3">Salida a comida</p>
                    <el-time-select
                        v-model="s.start_break"
                        class="el-form-item w-100"
                        :class="{'is-error': false}"
                        start="08:00"
                        step="00:5"
                        end="20:00"
                        placeholder="Hora"
                        format="hh:mm A"
                        clearable
                    />
                    <p class="!text-purple-600 mb-0 mt-3">Entrada de comida</p>
                    <el-time-select
                        v-model="s.end_break"
                        class="el-form-item w-100"
                        :class="{'is-error': false}"
                        start="08:00"
                        step="00:5"
                        end="20:00"
                        placeholder="Hora"
                        format="hh:mm A"
                        clearable
                    />
                    <p class="!text-purple-600 mb-0 mt-3">Salida <span class="text-danger">*</span></p>
                    <el-time-select
                        v-model="s.end_time"
                        class="el-form-item w-100"
                        :class="{'is-error': false}"
                        start="08:00"
                        step="00:5"
                        end="20:00"
                        placeholder="Hora"
                        format="hh:mm A"
                        clearable
                    />
                    <p class="text-danger fs-small text-center mt-3 mb-0" v-if="errors[i] && (!s.start_time || !s.end_time)">Completa entrada y salida.</p>
                    <p class="text-danger fs-small text-center mt-3 mb-0" v-if="errors[i] && (s.start_time && s.end_time)">Los horarios no son consecutivos.</p>
                </el-card>
            </el-col>
        </el-row>
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