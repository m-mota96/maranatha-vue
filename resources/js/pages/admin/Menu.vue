<script lang="ts" setup>
import { computed  } from 'vue';
import { usePage, Link, router } from '@inertiajs/vue3';

interface Module {
    name: string;
    icon?: string;
    target: string;
    submodules: [{
        name: string;
        target: string;
        submodules: [{
            name: string;
            target: string;
        }];
    }];
    [key: string]: any;
};

const page = usePage();
const user = page.props.auth.user;

const { dad, modules } = defineProps<{
    dad: string;
    modules: Module[];
}>();

const handleLogout = () => {
    router.flushAll();
};

const formattedModules = computed(() =>
    modules.map(m => ({
        ...m,
        icon: typeof m.icon === 'string' ? m.icon.split(', ') : []
    }))
);
</script>

<template>
    <el-row class="ps-2 pe-2">
        <el-menu class="el-menu-demo ms-4 me-3 mt-2" mode="horizontal">
            <el-menu-item :class="{'active': dad == 'Inicio'}" index="1">
                <a :href="route(`administrador.inicio`)" class="w-100 align-items">
                    <font-awesome-icon :icon="['fas', 'house-chimney']" />&nbsp;Inicio
                </a>
            </el-menu-item>
            <template v-for="(m, i) in formattedModules" :key="i">
                <el-menu-item :index="(i + 2).toString()" style="height: 100%;" v-if="!m.submodules">
                    <a :href="route(`administrador.${m.target}`)" class="w-100">
                        <font-awesome-icon :icon="[m.icon[0], m.icon[1]]" />&nbsp;{{ m.name }}
                    </a>
                </el-menu-item>
                <el-sub-menu :index="(i + 2).toString()" :class="{'active': dad == m.name}" style="height: 100%;" v-if="m.submodules">
                    <template #title>
                        <span class="align-items">
                            <font-awesome-icon :icon="[m.icon[0], m.icon[1]]" />&nbsp;{{ m.name }}
                        </span>
                    </template>
                    <template v-for="(s, j) in m.submodules" :key="j">
                        <el-menu-item :index="`${(i + 2)}-${(j + 1)}`" v-if="!s.submodules.length" style="color: black !important;">
                            <a :href="route(`administrador.${s.target}`)" class="w-100">{{ s.name }}</a>
                        </el-menu-item>
                        <el-sub-menu :index="`${i + 2}-${j + 1}`" v-if="s.submodules.length">
                            <template #title><span style="color: black !important;">{{ s.name }}</span></template>
                            <el-menu-item v-for="(ss, k) in s.submodules" :key="k" :index="`${(i + 2)}-${(j + 1)}-${k + 1}`" style="color: black !important;">
                                <a :href="route(`administrador.${ss.target}`)" class="w-100">{{ ss.name }}</a>
                            </el-menu-item>
                        </el-sub-menu>
                    </template>
                </el-sub-menu>
            </template>
            <el-menu-item class="none-item-one pb-2" index="998">
                <h5 class="mt-1" style="text-transform: capitalize !important; cursor: unset !important;">
                    <font-awesome-icon :icon="['fas', 'user-shield']" /> {{ user.name }}
                </h5>
            </el-menu-item>
            <el-menu-item class="none-item pb-2" index="999">
                <el-tooltip content="Cerrar sesión" effect="customized">
                    <div class="ps-3 pe-3 radius-top radius-bottom">
                        <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
                            <h3>
                                <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" />
                            </h3>
                        </Link>
                    </div>
                </el-tooltip>
            </el-menu-item>
        </el-menu>
    </el-row>
</template>

<style scoped>
.el-menu--horizontal {
    --el-menu-horizontal-height: 45px;
    border: unset !important;
    background: transparent !important;
}
.el-menu--horizontal div {
    --el-menu-horizontal-height: 35px;
    border: unset !important;
}
.el-menu {
    width: 100%;
}
.el-menu-item {
    border-top-left-radius: 0.42rem !important;
    border-top-right-radius: 0.42rem !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: white;
    font-weight: 600 !important;
    font-size: 0.8rem !important;
    text-transform: uppercase !important;
    margin-left: 8px;
    padding-top: 0px;
}
.el-menu-item:hover {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}
.el-sub-menu {
    border-top-left-radius: 0.42rem !important;
    border-top-right-radius: 0.42rem !important;
    background-color: rgba(255, 255, 255, 0.1) !important;
    margin-left: 8px;
    padding-top: 0px;
}
.el-sub-menu:hover {
    background-color: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}
.active {
    background: #ffffff !important;
    color: black !important;
}
.active span {
    color: black !important;
}
.active:hover {
    background: #ffffff !important;
    color: black !important;
}
.align-items {
    display: inline-flex;
    align-items: center;
}
.none-item {
    background: transparent !important;
    color: white !important;
    /* margin-right: auto !important; */
    right: 0 !important;
    position: absolute !important;
}
.none-item:hover {
    background: transparent !important;
    color: white !important;
}
.none-item div {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
}
.none-item-one {
    background: transparent !important;
    color: white !important;
    /* margin-right: auto !important; */
    right: 9vh !important;
    position: absolute !important;
    cursor: unset !important;
}
.none-item-one:hover {
    background: transparent !important;
    color: white !important;
}
.none-item-one div {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: white !important;
}
</style>