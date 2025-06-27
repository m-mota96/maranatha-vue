<script setup>
import { ref, onMounted } from 'vue';
import Menu from './Menu.vue';

const props = defineProps({
    menu: {
        type: Array
    },
    module: {
        type: Object,
        required: false,
    },
    dad: {
        type: String,
        required: false,
        default: 'Inicio'
    }
});

const currentYear = ref(new Date().getFullYear());
const title       = ref('Inicio');

onMounted(() => {
    if (props.module) {
        title.value = `${props.module.dad.name} - ${props.module.name}`;
    }
});
</script>

<template>
    <Menu :modules="props.menu" :dad="props.dad"></Menu>
    <el-row class="pl-3 pr-3">
        <el-col :span="24" class="radius-top  radius-bottom bg-white">
            <el-row>
                <el-col :span="24" class="pt-3 pb-3 ps-4 pe-4">
                    <h4>{{ title }}</h4>
                </el-col>
                <el-col :span="24" class="content radius-bottom p-3">
                    <el-row class="bg-white radius-top radius-bottom p-3" style="height: 100%;">
                        <slot />
                    </el-row>
                </el-col>
            </el-row>
        </el-col>
        <el-col :span="8" class="pt-2">
            <b class="text-white fs-6">{{ currentYear }} © Temazcal Maranatha</b>
        </el-col>
        <el-col :span="8" class="pt-2 justify-center items-center" style="display: flex;">
            <img class="w-20" src="/general/icono3.png" alt="Temazcal Maranatha">
        </el-col>
        <el-col :span="8" class="pt-2">

        </el-col>
    </el-row>
</template>

<style scoped>
    .content {
        min-height: 79vh !important;
        background-color: #ebedf6 !important;
    }
</style>