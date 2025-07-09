<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose, computed } from 'vue';

const { getParentServices, serviceType } = defineProps({
    getParentServices: Function,
    serviceType: Array
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const service       = ref({
    id: null,
    service_type_id: '',
    name: '',
    price : '',
    discounted_price: '',
    time: 15
});
const errors = ref({
    service_type_id: false,
    name: false,
    price: false,
    discounted_price: false,
    time: false
});

const showModal = (_service) => {
    resetErrors();
    title.value                    = 'Crear nuevo servicio';
    button.value                   = 'Guardar';
    service.value.id               = null;
    service.value.service_type_id  = '';
    service.value.name             = '';
    service.value.price            = '';
    service.value.discounted_price = '';
    service.value.time             = 15;
    if (_service) {
        title.value                    = 'Editar servicio';
        button.value                   = 'Guardar cambios';
        service.value.id               = _service.id;
        service.value.service_type_id  = _service.service_type_id;
        service.value.name             = _service.name;
        service.value.price            = _service.price;
        service.value.discounted_price = _service.discounted_price;
        service.value.time             = _service.time;
    }
    dialogVisible.value = true;
};

const saveService = async () => {
    if (validate()) {
        const method   = !service.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/service', method, service.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        getParentServices();
        showNotification(response.msj);
    }
};


const validate = () => {
    resetErrors();
    let valid = true;
    if (!service.value.service_type_id) {
        errors.value.service_type_id = true;
        valid                        = false;
    }
    if (!service.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!service.value.price) {
        errors.value.price = true;
        valid              = false;
    }
    // if (!service.value.discounted_price) {
    //     errors.value.discounted_price = true;
    //     valid                         = false;
    // }
    if (!service.value.time) {
        errors.value.time = true;
        valid             = false;
    }
    return valid;
};

const resetErrors = () => {
    errors.value.service_type_id  = false;
    errors.value.name             = false;
    errors.value.price            = false;
    errors.value.discounted_price = false;
    errors.value.time = false;
};

const getLabel = (n) => {
    let label = `${n} minutos`
    if (n % 60 === 0) {
        label += ` - ${n / 60} ${n / 60 === 1 ? 'hora' : 'horas'}`
    }
    return label
};

const isNumber = (evt) => {
    const charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode < 48 || charCode > 57) {
        evt.preventDefault();
    }
};

const multiplesOf15 = computed(() => {
    const result = []
    for (let i = 15; i <= 240; i += 15) {
        result.push(i)
    }
    return result
});

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="500"
        style="margin-top: 2% !important;"
    >
        <el-col :span="24" class="mb-3">
            <label for="serviceType" class="bold">Tipo de servicio <span class="text-danger">*</span></label>
            <el-select
                class="el-form-item"
                :class="{'is-error': errors.service_type_id}"
                id="serviceType"
                v-model="service.service_type_id"
                placeholder="Elige una opción"
            >
                <el-option
                    v-for="s in serviceType"
                    :key="s.id"
                    :value="s.id"
                    :label="s.name"
                />
            </el-select>
            <span class="text-danger fs-small" v-if="errors.service_type_id">El tipo de servicio es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="name" class="bold">Servicio <span class="text-danger">*</span></label>
            <el-input v-model="service.name" class="el-form-item" :class="{'is-error': errors.name}" id="name" />
            <span class="text-danger fs-small" v-if="errors.name">El nombre del servicio es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="price" class="bold">Precio <span class="text-danger">*</span></label>
            <el-input
                v-model="service.price"
                class="el-form-item"
                :class="{'is-error': errors.price}"
                id="price"
                :formatter="(value) => `$ ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
                @keypress="isNumber($event)"
            />
            <span class="text-danger fs-small" v-if="errors.name">El precio es obligatorio.</span>
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="discounted_price" class="bold">Precio con descuento</label>
            <el-input
                v-model="service.discounted_price"
                class="el-form-item"
                :class="{'is-error': errors.discounted_price}"
                id="discounted_price"
                :formatter="(value) => `$ ${value}`.replace(/\B(?=(\d{3})+(?!\d))/g, ',')"
                :parser="(value) => value.replace(/\$\s?|(,*)/g, '')"
                @keypress="isNumber($event)"
            />
        </el-col>
        <el-col :span="24" class="mb-3">
            <label for="time" class="bold">Duración del servicio <span class="text-danger">*</span></label>
            <!-- <el-input v-model="service.time" class="el-form-item" :class="{'is-error': errors.time}" id="time" /> -->
             <el-select
                class="el-form-item"
                :class="{'is-error': errors.time}"
                id="serviceType"
                v-model="service.time"
                placeholder="Elige una opción"
            >
                <el-option
                    v-for="n in multiplesOf15"
                    :key="n"
                    :value="n"
                    :label="getLabel(n)"
                />
            </el-select>
            <span class="text-danger fs-small" v-if="errors.time">La duración del servicio es obligatoria.</span>
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveService">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>

</style>