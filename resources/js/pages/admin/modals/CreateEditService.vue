<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose, computed } from 'vue';

const { getParentServices } = defineProps({
    getParentServices: Function
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const serviceType   = ref([]);
const service       = ref({
    id: null,
    service_type_id: '',
    service_type_name: '',
    name: '',
    price : '',
    discounted_price: '',
    time: 15,
    color: '',
    require_staff: ''
});
const errors = ref({
    service_type_id: false,
    name: false,
    price: false,
    discounted_price: false,
    time: false,
    require_staff: false
});
const isAdding   = ref(false);
const optionName = ref('');

const getServiceType = async () => {
    const response = await apiClient('admin/serviceTypes');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    serviceType.value = response.data;
};

const showModal = async (_service) => {
    await getServiceType();
    resetErrors();
    title.value                    = 'Crear nuevo servicio';
    button.value                   = 'Guardar';
    service.value.id               = null;
    service.value.service_type_id  = '';
    service.value.name             = '';
    service.value.price            = '';
    service.value.discounted_price = '';
    service.value.time             = 15;
    service.value.color            = '';
    if (_service) {
        title.value                    = 'Editar servicio';
        button.value                   = 'Guardar cambios';
        service.value.id               = _service.id;
        service.value.service_type_id  = _service.service_type_id;
        service.value.name             = _service.name;
        service.value.price            = _service.price;
        service.value.discounted_price = _service.discounted_price;
        service.value.time             = _service.time;
        service.value.color            = _service.color;
    }
    dialogVisible.value = true;
};

const saveService = async () => {
    if (validate()) {
        const method   = !service.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/service', method, {
            service: service.value,
            serviceType: serviceType.value.filter(s => s.newRecord)
        });
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
    if (!service.value.require_staff) {
        errors.value.require_staff = true;
        valid                      = false;
    }
    return valid;
};

const resetErrors = () => {
    errors.value.service_type_id  = false;
    errors.value.name             = false;
    errors.value.price            = false;
    errors.value.discounted_price = false;
    errors.value.time             = false;
    errors.value.require_staff    = false;
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

const onAddOption = () => {
    isAdding.value = true;
};

const onConfirm = () => {
    if (optionName.value) {
        const maxId = Math.max(...serviceType.value.map(st => st.id));
        serviceType.value.push({
            id: maxId + 1,
            name: optionName.value,
            newRecord: true
        });
        clear();
    }
};

const clear = () => {
    optionName.value = '';
    isAdding.value   = false;
};

const setName = (val) => {
    const findService               = serviceType.value.find(st => st.id === val);
    service.value.service_type_name = findService.name;
};

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
            <!-- <el-select
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
            </el-select> -->
            <el-select v-model="service.service_type_id" placeholder="Elige una opción" @change="setName">
                <el-option
                    v-for="s in serviceType"
                    :key="s.id"
                    :label="s.name"
                    :value="s.id"
                />
                <template #footer>
                    <el-button v-if="!isAdding" text bg size="small" @click="onAddOption">
                        Añadir opción
                    </el-button>
                    <template v-else>
                        <el-input
                            v-model="optionName"
                            class="option-input mb-2"
                            placeholder="Escribe la opción"
                            size="small"
                        />
                        <el-button type="primary" size="small" @click="onConfirm">
                            Confirmar
                        </el-button>
                        <el-button size="small" @click="clear">Cancelar</el-button>
                    </template>
                </template>
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
        <el-col :span="24" class="mb-3">
            <label for="color" class="bold">Color</label><br>
            <el-color-picker class="w-100" v-model="service.color" />
            <!-- <el-color-picker-panel v-model="service.color" /> -->
        </el-col>
        <el-col :span="24" class="mb-3">
            <label class="bold">¿El servicio requiere ser realizado por alguien del Staff? <span class="text-danger">*</span></label><br>
            <el-radio-group v-model="service.require_staff">
                <el-radio :value="true">Si</el-radio>
                <el-radio :value="false">No</el-radio>
            </el-radio-group><br>
            <span class="text-danger fs-small" v-if="errors.require_staff">Elige una opción.</span>
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