<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';
import CreateEditProduct from './modals/CreateEditProduct.vue';

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

const createEditProductRef = ref(null);
const products             = ref([]);
const pagination           = ref({
    currentPage: 1,
    pageSize: 50,
    total: 0
});
const search = ref({
    name: '',
    brand:'',
    price: '',
    discounted_price: '',
    status: ''
});
const order = ref({
    orderBy: 'created_at',
    order: 'DESC'
});

onMounted(() => {
    getProducts();
});

const getProducts = async () => {
    const response = await apiClient('admin/products', 'GET', {pagination: pagination.value, search: search.value, order: order.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7500);
        return
    }
    products.value = response.data.products;
    pagination.value.total = response.data.totalRows;
};

const statusProduct = async (_product) => {
    _product.status = _product.status ? false : true;
    const response  = await apiClient('admin/product', 'PUT', _product);
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    showNotification(response.msj);
};

const deleteProduct = async (id) => {
    const response = await apiClient(`admin/product/${id}`, 'DELETE');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    getProducts();
    showNotification(response.msj);
};

const openModal = (data = null) => {
    createEditProductRef.value?.showModal(data);
};

const resetFilters = () => {
    search.value.name             = '';
    search.value.brand            = '';
    search.value.price            = '';
    search.value.discounted_price = '';
    search.value.status           = '';
    order.value.orderBy           = 'created_at';
    order.value.order             = 'DESC';
    getProducts();
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('es-MX', {
        style: 'currency',
        currency: 'MXN'
    }).format(value);
};

const handleSizeChange = (val) => {
    getProducts();
}
const handleCurrentChange = (val) => {
    getProducts();
}
</script>

<template>
    <Layout :menu="menu" :module="module">
        <el-col class="mb-2" :span="4" :offset="15">
            <label for="order">Ordenar por</label>
            <el-select v-model="order.orderBy" @change="getProducts" id="order">
                <el-option :key="0" label="Fecha de creación" value="created_at" />
                <el-option :key="1" label="Nombre del producto" value="name" />
                <el-option :key="2" label="Precio" value="price" />
                <el-option :key="3" label="Precio con descuento" value="discounted_price" />
            </el-select>
        </el-col>
        <el-col class="mb-2 ps-3" :span="4">
            <br>
            <el-select v-model="order.order" @change="getProducts">
                <el-option :key="0" label="Ascendente" value="ASC" />
                <el-option :key="1" label="Descendente" value="DESC" />
            </el-select>
        </el-col>
        <el-col class="text-center" :span="1">
            <br>
            <el-tooltip
                class="box-item"
                effect="customized"
                content="Limpiar filtros"
                placement="top"
            >
                <font-awesome-icon class="mt-2 pointer" :icon="['fas', 'filter-circle-xmark']" @click="resetFilters" />
            </el-tooltip>
        </el-col>
        <el-col :span="24" class="table-wrapper">
            <el-table :data="products" stripe empty-text="Ningún dato disponible en esta tabla" header-cell-class-name="text-dark bold">
                <el-table-column prop="id" label="#" width="70" align="center" />
                <el-table-column prop="barcode" label="Código de barras" />
                <el-table-column>
                    <template #header>
                        <el-input placeholder="Nombre del producto" title="Escribe para buscar" v-model="search.name" @input="getProducts" clearable />
                    </template>
                    <template #default="scope">
                        {{ scope.row.name }} 
                        {{ scope.row.content ? scope.row.content : '' }} 
                        {{ scope.row.abreviation ? scope.row.abreviation : '' }}
                    </template>
                </el-table-column>
                <el-table-column prop="brand">
                    <template #header>
                        <el-input placeholder="Marca" title="Escribe para buscar" v-model="search.brand" @input="getProducts" clearable />
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-input placeholder="Precio" title="Escribe para buscar" v-model="search.price" @input="getProducts" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.price) }}
                    </template>
                </el-table-column>
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-input placeholder="Precio con descuento" title="Escribe para buscar" v-model="search.discounted_price" @input="getProducts" clearable />
                    </template>
                    <template #default="scope">
                        {{ formatCurrency(scope.row.discounted_price) }}
                    </template>
                </el-table-column>
                <el-table-column label="Existencias" align="center">
                    <template #default="scope">
                        <span v-if="scope.row.type_sale === 'pza'">
                            {{ parseInt(scope.row.inputs - scope.row.outputs) }}
                        </span>
                        <span v-if="scope.row.type_sale === 'kg'">
                            {{ scope.row.inputs - scope.row.outputs }}
                        </span>
                    </template>
                </el-table-column>
                <el-table-column prop="description" label="Descripción" />
                <el-table-column align="center" width="150">
                    <template #header>
                        <el-select placeholder="Estatus" v-model="search.status" @change="getProducts" clearable>
                            <el-option :value="1" label="Activo" />
                            <el-option :value="0" label="Inactivo" />
                        </el-select>
                    </template>
                    <template #default="scope">
                        <span class="text-success bold" v-if="scope.row.status">Activo</span>
                        <span class="text-danger bold" v-if="!scope.row.status">Inactivo</span>
                    </template>
                </el-table-column>
                <el-table-column width="150" align="center">
                    <template #header>
                        <el-tooltip content="Nuevo producto" effect="customized" placement="top">
                            <el-button class="btn-success" @click="openModal()">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                            </el-button>
                        </el-tooltip>
                    </template>
                    <template #default="scope">
                        <el-button-group>
                            <el-tooltip content="Editar producto" effect="customized" placement="top">
                                <el-button class="btn-success" @click="openModal(scope.row)">
                                    <font-awesome-icon :icon="['fas', 'pen']" />
                                </el-button>
                            </el-tooltip>
                            <el-tooltip :content="scope.row.status ? 'Desactivar producto' : 'Activar producto'" effect="customized" placement="top">
                                <el-button
                                    :class="{'btn-warning': scope.row.status, 'btn-info': !scope.row.status}"
                                    @click="statusProduct(scope.row)"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </el-button>
                            </el-tooltip>
                            <el-popconfirm
                                class="box-item"
                                confirm-button-text="Eliminar"
                                cancel-button-text="Cancelar"
                                :hide-icon="true"
                                confirm-button-type="danger"
                                cancel-button-type="primary"
                                :width="200"
                                title="¿Seguro que deseas eliminar este producto?"
                                placement="left"
                                @confirm="deleteProduct(scope.row.id)"
                            >
                                <template #reference>
                                    <span>
                                        <el-tooltip content="Eliminar producto" effect="customized" placement="top">
                                            <el-button class="btn-danger" style="border-top-left-radius: 0px; border-bottom-left-radius: 0px;">
                                                <font-awesome-icon :icon="['fas', 'trash-can']" />
                                            </el-button>
                                        </el-tooltip>
                                    </span>
                                </template>
                            </el-popconfirm>
                        </el-button-group>
                    </template>
                </el-table-column>
            </el-table>
            <el-pagination
                class="mt-3 custom-pager"
                v-model:current-page="pagination.currentPage"
                v-model:page-size="pagination.pageSize"
                :page-sizes="[50, 100, 150, 200, 250]"
                layout="sizes, prev, pager, next"
                :total="pagination.total"
                @size-change="handleSizeChange"
                @current-change="handleCurrentChange"
            />
        </el-col>
    </Layout>
    <CreateEditProduct ref="createEditProductRef" :get-parent-products="getProducts" />
</template>

<style scoped>
.table-wrapper {
    display: block;
    min-height: 100%;
}
</style>