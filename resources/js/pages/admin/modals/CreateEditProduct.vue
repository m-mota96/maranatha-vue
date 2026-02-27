<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentProducts } = defineProps({
    getParentProducts: Function
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const product       = ref({
    id: null,
    name: '',
    barcode: '',
    brand: '',
    content: 0,
    abreviation: '',
    type_sale: '',
    price: 0,
    discounted_price: 0,
    lote: '',
    expiration_date: '',
    description: ''
});

const errors = ref({
    name: false,
    barcode: false,
    price: false,
    type_sale: false
});

const showModal = (_product) => {
    resetErrors();
    title.value                    = 'Crear nuevo producto';
    button.value                   = 'Guardar';
    product.value.id               = null;
    product.value.name             = '';
    product.value.barcode          = '';
    product.value.brand            = '';
    product.value.content          = 0;
    product.value.abreviation      = '';
    product.value.type_sale        = '';
    product.value.price            = 0;
    product.value.discounted_price = 0;
    product.value.description      = '';
    if (_product) {
        title.value                    = 'Editar producto';
        button.value                   = 'Guardar cambios';
        product.value.id               = _product.id;
        product.value.name             = _product.name;
        product.value.barcode          = _product.barcode;
        product.value.brand            = _product.brand;
        product.value.content          = _product.content;
        product.value.abreviation      = _product.abreviation;
        product.value.type_sale        = _product.type_sale;
        product.value.price            = parseFloat(_product.price);
        product.value.discounted_price = parseFloat(_product.discounted_price);
        product.value.description      = _product.description;
    }
    dialogVisible.value = true;
};

const resetErrors = () => {
    errors.value.name      = false;
    errors.value.barcode   = false;
    errors.value.price     = false;
    errors.value.type_sale = false;
};

const validate = () => {
    resetErrors();
    let valid = true;
    if (!product.value.name) {
        errors.value.name = true;
        valid             = false;
    }
    if (!product.value.barcode) {
        errors.value.barcode = true;
        valid                = false;
    }
    if (!product.value.price) {
        errors.value.price = true;
        valid              = false;
    }
    if (!product.value.type_sale) {
        errors.value.type_sale = true;
        valid                  = false;
    }
    return valid;
};

const saveProduct = async () => {
    if (validate()) {
        const method   = !product.value.id ? 'POST' : 'PUT';
        const response = await apiClient('admin/product', method, product.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        getParentProducts();
        showNotification(response.msj);
    }
};

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        :title="title"
        width="800"
        style="margin-top: 2% !important;"
    >
        <el-row :gutter="30">
            <el-col :span="12" class="mb-3">
                <label for="barcode" class="bold">Código de barras/clave <span class="text-danger">*</span></label>
                <el-input v-model="product.barcode" class="el-form-item" :class="{'is-error': errors.barcode}" id="barcode" clearable />
                <span class="text-danger fs-small" v-if="errors.barcode">El código de barras/clave es obligatorio/a.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="name" class="bold">Nombre del producto <span class="text-danger">*</span></label>
                <el-input v-model="product.name" class="el-form-item" :class="{'is-error': errors.name}" id="name" clearable />
                <span class="text-danger fs-small" v-if="errors.name">El nombre es obligatorio.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="price" class="bold">Precio <span class="text-danger">*</span></label>
                <el-input-number
                    v-model="product.price"
                    class="el-form-item w-100"
                    :class="{'is-error': errors.price}"
                    id="price"
                    clearable
                    :presicion="2"
                    :step=".01"
                    :min="0"
                    :controls="false"
                >
                    <template #prefix>$</template>
                </el-input-number>
                <span class="text-danger fs-small" v-if="errors.price">El precio es obligatorio.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="discounted_price" class="bold">Precio con descuento</label>
                <el-input-number
                    v-model="product.discounted_price"
                    class="el-form-item w-100"
                    id="discounted_price"
                    clearable
                    :presicion="2"
                    :step=".01"
                    :min="0"
                    :controls="false"
                >
                    <template #prefix>$</template>
                </el-input-number>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="type_sale" class="bold">¿Como se vende el producto? <span class="text-danger">*</span></label>
                <el-radio-group v-model="product.type_sale">
                    <el-radio value="pza">Por pieza</el-radio>
                    <el-radio value="kg">Por kilo</el-radio>
                </el-radio-group>
                <p class="text-danger fs-small mb-0" v-if="errors.type_sale">Marca una de las opciones.</p>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label class="bold">Contenido</label>
                <el-input
                    v-model="product.content"
                    class="input-with-select"
                >
                    <template #append>
                        <el-select v-model="product.abreviation" placeholder="Elige una opción" clearable style="width: 150px" >
                            <el-option label="Kg (kilogramos)" value="Kg." />
                            <el-option label="gr (gramos)" value="gr." />
                            <el-option label="L (litros)" value="L." />
                            <el-option label="ml (mililitros)" value="ml." />
                            <el-option label="pzas (piezas)" value="pzas." />
                            <el-option label="oz (onzas)" value="oz." />
                        </el-select>
                    </template>
                </el-input>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="brand" class="bold">Marca</label>
                <el-input v-model="product.brand" class="el-form-item" id="brand" clearable />
            </el-col>
            <el-col :span="24">
                <label class="bold">Descripción del producto</label>
                <el-mention
                    v-model="product.description"
                    type="textarea"
                    :rows="5"
                />
            </el-col>
            <el-col :span="25" class="pt-4 pb-4">
                <p class="text-black text-base">
                    <b class="text-red-500">NOTA:</b>
                    Si el producto no cuenta con código de barras ingresa una clave única que 
                    no exista en tus registros y que puedas identificar o recordar fácilmente.
                </p>
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveProduct">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>