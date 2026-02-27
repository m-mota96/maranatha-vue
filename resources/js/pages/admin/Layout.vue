<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Menu from './Menu.vue';

const appName                           = import.meta.env.VITE_APP_NAME;
const { menu, dad, module, background } = defineProps({
    menu: {
        type: Array,
        required: true
    },
    dad: {
        type: String,
        required: false,
        default: 'Inicio'
    },
    module: {
        type: Object,
        required: false,
        default: {
            name: '',
            dad: {
                name: 'Inicio'
            }
        }
    },
    background: {
        type: Boolean,
        required: false,
        default: true
    }
});

const currentYear = ref(new Date().getFullYear());
const title       = ref('Inicio');
const dadName     = ref('');

onMounted(() => {
    if (module?.dad?.name) {
        const moduleName = module.name ? `- ${module.name}` : '';
        title.value = `${module.dad.name} ${moduleName}`;
    } else {
        title.value = module.dad.name;
    }
    if (module?.dad?.dad?.name) {
        dadName.value = module.dad.dad.name;
    } else {
        dadName.value = module.dad.name;
    }
});
</script>

<template>
    <Menu :modules="menu" :dad="dadName"></Menu>
    <el-row class="pl-3 pr-3">
        <el-col :span="24" class="radius-top  radius-bottom bg-white">
            <el-row>
                <el-col :span="24" class="pt-3 pb-3 ps-4 pe-4">
                    <h4>{{ title }}</h4>
                </el-col>
                <el-col :span="24" class="content radius-bottom p-3">
                    <el-row :class="{'bg-white radius-top radius-bottom p-3': background}" style="height: 100%;">
                        <slot />
                    </el-row>
                </el-col>
            </el-row>
        </el-col>
        <el-col :span="8" class="pt-2 ps-2">
            <span class="text-white fs-6">{{ currentYear }} © {{ appName }}</span>
        </el-col>
        <el-col :span="8" class="pt-2 justify-center items-center" style="display: flex;">
            <img class="w-15" src="/general/icono3.png" :alt="appName">
        </el-col>
        <el-col :span="8" class="pt-2 pe-2 text-right">
            <span class="text-white fs-6">Desarrollado por <b>DataSoft Software</b></span>
        </el-col>
    </el-row>
</template>

<style scoped>
    .content {
        min-height: 77vh !important;
        background-color: #ebedf6 !important;
    }
</style>