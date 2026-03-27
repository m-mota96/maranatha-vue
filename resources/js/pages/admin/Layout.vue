<script lang="js" setup>
import { ref, onMounted, onUnmounted } from 'vue';
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
    window.addEventListener('scroll', handleScroll);
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

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const isVisible = ref(false);

// Función para mostrar/ocultar el botón según el scroll
const handleScroll = () => {
    isVisible.value = window.scrollY > 300; // Aparece tras bajar 300px
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};
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
        <transition name="fade">
            <button 
                v-if="isVisible" 
                @click="scrollToTop" 
                class="scroll-to-top"
            >
                <font-awesome-icon :icon="['fas', 'arrow-up']" />
            </button>
        </transition>
    </el-row>
</template>

<style scoped>
    .content {
        min-height: 77vh !important;
        background-color: #ebedf6 !important;
    }
    .scroll-to-top {
        position: fixed;
        bottom: 80px;
        right: 30px;
        padding: 5px 15px;
        background-color: rgb(137, 80, 252);
        color: white;
        border: none;
        border-radius: 15%;
        cursor: pointer;
        font-size: 24px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        z-index: 1000;
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.3s ease;
    }
    .fade-enter-from, .fade-leave-to {
        opacity: 0;
    }
</style>