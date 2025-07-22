<script lang="js" setup>
import { ref } from 'vue';
import Layout from './Layout.vue';
import CreateEditCustomer from './modals/CreateEditCustomer.vue';

const { menu } = defineProps({
    menu: {
        type: Array,
        required: true
    }
});

const createEditCustomerRef = ref(null);

const newCustomer = (data = null) => {
    createEditCustomerRef.value?.showModal(data);
};
</script>

<template>
    <Layout :menu="menu" :background="false">
        <el-col :span="24">
            <el-row :gutter="20">
                <el-col :span="18">
                    <el-card class="p-6">
        
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="p-6">
                        <el-row :gutter="20">
                            <el-col :span="12" class="mb-3">
                                <div class="card-warning p-4">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'clipboard']" /></h1>
                                    <h6 class="text-center">Nueva cita</h6>
                                </div>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div  class="card-danger p-4">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'dollar-sign']" /></h1>
                                    <h6 class="text-center">Nueva venta</h6>
                                </div>
                            </el-col>
                            <el-col :span="12" class="mb-3">
                                <div  class="card-info p-4" @click="newCustomer()">
                                    <h1 class="text-center"><font-awesome-icon :icon="['fas', 'user-plus']" /></h1>
                                    <h6 class="text-center">Nuevo miembro</h6>
                                </div>
                            </el-col>
                        </el-row>
                    </el-card>
                </el-col>
            </el-row>
        </el-col>
    </Layout>
    <CreateEditCustomer ref="createEditCustomerRef" />
</template>

<style scoped>
.card-warning {
    color: #FFA800;
    background-color: #FFF4DE;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-warning:hover {
    background-color: #FFA800;
    color: white;
}
.card-danger {
    color: #F64E60;
    background-color: #FFE2E5;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-danger:hover {
    background-color: #F64E60;
    color: white;
}
.card-info {
    color: #8950FC;
    background-color: #EEE5FF;
    border-color: transparent;
    cursor: pointer;
    border-radius: 15px;
}
.card-info:hover {
    background-color: #8950FC;
    color: white;
}
</style>