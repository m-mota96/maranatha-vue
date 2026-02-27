<script lang="js" setup>
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import { ref, defineExpose } from 'vue';

const { getParentInventory, references } = defineProps({
    getParentInventory: Function,
    references: Array
});

const title         = ref('');
const button        = ref('');
const dialogVisible = ref(false);
const inventory     = ref({
    product_id: null,
    product_name: '',
    reference_id: null,
    type: '',
    quantity: 0,
    expiration_date: '',
    batch: '',
    product_cost: 0,
    description: '',
});
const errors = ref({
    product: false,
    reference: false,
    quantity: false,
    type: false,
});

const showModal = () => {
    resetErrors();
    title.value                     = 'Registrar inventario';
    button.value                    = 'Guardar';
    inventory.value.product_id      = null;
    inventory.value.product_name    = '';
    inventory.value.reference_id    = null;
    inventory.value.type            = '';
    inventory.value.quantity        = 0;
    inventory.value.expiration_date = '';
    inventory.value.batch           = '';
    inventory.value.product_cost    = 0;
    inventory.value.description     = '';
    inventory.value.type_sale       = 'pza';
    dialogVisible.value = true;
};

const resetErrors = () => {
    errors.value.product   = false;
    errors.value.reference = false;
    errors.value.quantity  = false;
    errors.value.type      = false;
};

const validate = () => {
    resetErrors();
    let valid = true;
    if (!inventory.value.product_id) {
        errors.value.product = true;
        valid                = false;
    }
    if (!inventory.value.reference_id) {
        errors.value.reference = true;
        valid                  = false;
    }
    if (!inventory.value.quantity) {
        errors.value.quantity = true;
        valid                 = false;
    }
    if (!inventory.value.type) {
        errors.value.type = true;
        valid             = false;
    }
    return valid;
};

const saveInventory = async () => {
    if (validate()) {
        const response = await apiClient('admin/inventory', 'POST', inventory.value);
        if (response.error) {
            showNotification(response.msj, '¡Error!', 'error', 7500);
            return
        }
        dialogVisible.value = false;
        getParentInventory();
        showNotification(response.msj);
    }
};

const querySearch = async (queryString, cb) => {
    if (queryString.length < 3) {
        cb([]);
        return;
    }

    const response = await apiClient('admin/product', 'GET', { name: queryString });
    const results  = response.data
    .filter(createFilter(queryString))
    .map(product => ({
        ...product,
        value: `
            ${product.name} ${product.content ? product.content : ''} ${product.abreviation ? product.abreviation : ''} ${product.brand ? '('+product.brand+')' : ''}
        `
    }));

    cb(results);
};

const createFilter = (queryString) => {
    const search = queryString.toLowerCase();

    return (product) => {
        return product.name.toLowerCase().includes(search);
    };
};

const handleSelect = (_product)=> {
    inventory.value.product_id = _product.id;
    inventory.value.type_sale  = _product.type_sale;
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
                <label for="product" class="bold">Nombre del producto <span class="text-danger">*</span></label>
                <el-autocomplete
                    v-model="inventory.product_name"
                    class="el-form-item"
                    :class="{'is-error': errors.product}"
                    id="product"
                    :fetch-suggestions="querySearch"
                    :trigger-on-focus="false"
                    clearable
                    placeholder="Escribe el nombre del producto para buscar"
                    @select="handleSelect"
                />
                <span class="text-danger fs-small" v-if="errors.product">Debes elegir un producto de la lista de sugerencias.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="reference" class="bold">Referencia <span class="text-danger">*</span></label>
                <el-select
                    v-model="inventory.reference_id"
                    class="el-form-item"
                    :class="{'is-error': errors.reference}"
                    id="reference"
                    placeholder="Elige una opción"
                    clearable
                >
                    <el-option v-for="r in references" :key="r.id" :label="r.name" :value="r.id" />
                </el-select>
                <span class="text-danger fs-small" v-if="errors.reference">La referencia es obligatoria.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="quantity" class="bold">Cantidad <span class="text-danger">*</span></label>
                <el-input-number
                    v-if="inventory.type_sale === 'kg'"
                    v-model="inventory.quantity"
                    class="el-form-item w-100"
                    :class="{'is-error': errors.quantity}"
                    :precision="2"
                    :step="0.01"
                    :min="0"
                    :controls="false"
                />
                <el-input-number
                    v-if="inventory.type_sale === 'pza'"
                    v-model="inventory.quantity"
                    class="el-form-item w-100"
                    :class="{'is-error': errors.quantity}"
                    :precision="0"
                    :step="1"
                    :min="0"
                    :controls="false"
                />
                <span class="text-danger fs-small" v-if="errors.quantity">La cantidad es obligatoria.</span>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="type" class="bold">Tipo de movimiento <span class="text-danger">*</span></label><br>
                <el-radio-group v-model="inventory.type">
                    <el-radio value="input">Ingreso</el-radio>
                    <el-radio value="output">Egreso</el-radio>
                </el-radio-group>
                <p class="text-danger fs-small mb-0" v-if="errors.type">Marca una de las opciones.</p>
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="batch" class="bold">Lote</label>
                <el-input v-model="inventory.batch" id="batch" clearable />
            </el-col>
            <el-col :span="12" class="mb-3">
                <label for="expiration_date" class="bold">Fecha de caducidad</label>
                <el-date-picker
                    style="width: 100%;"
                    v-model="inventory.expiration_date"
                    id="expiration_date"
                    type="date"
                    placeholder="Selecciona la fecha"
                    clearable
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                />
            </el-col>
            <el-col :span="12" class="mb-3" v-if="inventory.type === 'input' && inventory.reference_id === 1">
                <label for="product_cost" class="bold">¿Cuál fue el monto pagado por el producto?</label>
                <el-input-number
                    v-model="inventory.product_cost"
                    class="el-form-item w-100"
                    id="product_cost"
                    clearable
                    :precision="2"
                    :step="0.01"
                    :min="0"
                    :controls="false"
                >
                    <template #prefix><span class="text-dark">$</span></template>
                </el-input-number>
            </el-col>
            <el-col :span="24">
                <label class="bold">Comentarios</label>
                <el-mention
                    v-model="inventory.description"
                    type="textarea"
                    :rows="5"
                />
            </el-col>
        </el-row>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="saveInventory">
                    {{ button }}
                </el-button>
            </div>
        </template>
    </el-dialog>
</template>