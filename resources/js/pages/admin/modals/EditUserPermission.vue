<script lang="js" setup>
import { onMounted, ref, defineExpose } from 'vue';
import apiClient from '@/apiClient';
import { showNotification } from '@/notification';

const dialogVisible = ref(false);
const modules       = ref([]);
const activeModules = ref([]);
const userId        = ref(0);

const { getParentUsers } = defineProps({
    getParentUsers: Function
});

onMounted(() => {
    getModules();
});

const getModules = async () => {
    const response = await apiClient('admin/allModulesMenu');
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error');
        return
    }
    modules.value = response.data;
};

const activateModuleTree = (module) => {
    const dad_id                   = module.dad ? module.dad.id : null;
    const grandfather_id           = dad_id ? (module.dad.dad ? module.dad.dad.id : null) : null;
    activeModules.value[module.id] = { id: module.id, dad_id, grandfather_id, active: false, name: module.name };

    if (Array.isArray(module.submodules)) {
        module.submodules.forEach(sub => activateModuleTree(sub));
    }
}

const showModal = (_userId, userModules) => {
    userId.value = _userId;
    modules.value.forEach(activateModuleTree);
    
    const userModuleIds = new Set(userModules.map(um => um.id));

    activeModules.value.forEach(am => {
        if (userModuleIds.has(am.id)) {
            am.active = true;
        }
    });
    dialogVisible.value = true;
}

const enableDisableModule = (value, moduleId = null, dadId = null, grandfatherId = null) => {
    let status = false, status2 = false;
    if (dadId) {
        activeModules.value.forEach(am => {
            if (am.dad_id === dadId && am.active) {
                status = true;
            }
        });
        activeModules.value[dadId].active = true;
        if (!status) {
            activeModules.value[dadId].active = false;
        }
    }
    if (grandfatherId) {
        activeModules.value.forEach(am => {
            if (am.dad_id === grandfatherId && am.active) {
                status2 = true;
            }
        });
        activeModules.value[grandfatherId].active = true;
        if (!status2) {
            activeModules.value[grandfatherId].active = false;
        }
    }
    if (moduleId) {
        activeModules.value.forEach(am => {
            if (am.dad_id === moduleId || am.grandfather_id === moduleId) {
                am.active = value;
            }
        });
    }
};

const savePermission = async () => {
    const response = await apiClient('admin/permissionUser', 'POST', {user_id: userId.value, modules: activeModules.value});
    if (response.error) {
        showNotification(response.msj, '¡Error!', 'error', 7000);
        return
    }
    dialogVisible.value = false;
    getParentUsers();
    showNotification(response.msj);
}

defineExpose({
    showModal
});
</script>

<template>
    <el-dialog
        v-model="dialogVisible"
        title="Editar permisos"
        width="500"
        style="margin-top: 2% !important;"
    >
        <el-col v-for="(m, i) in modules" :key="i">
            <ul>
                <li>
                    <el-checkbox class="bold text-success boxes" :label="` Módulo - ${ m.name }`" v-model="activeModules[m.id].active" @change="(val) => enableDisableModule(val, activeModules[m.id].id)" />
                </li>
                <ul v-for="(s, j) in m.submodules" :key="j">
                    <li>
                        <el-checkbox class="bold text-primary boxes" :label="` Módulo - ${ s.name }`" v-model="activeModules[s.id].active" @change="(val) => enableDisableModule(val, activeModules[s.id].id, activeModules[s.id].dad_id)" />
                    </li>
                    <ul v-for="(sm, k) in s.submodules" :key="k">
                        <li>
                            <el-checkbox class="bold text-info boxes" :label="` Módulo - ${ sm.name }`" v-model="activeModules[sm.id].active" @change="(val) => enableDisableModule(val, null, activeModules[sm.id].dad_id, activeModules[sm.id].grandfather_id)" />
                        </li>
                    </ul>
                </ul>
            </ul>
        </el-col>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogVisible = false">Cancelar</el-button>
                <el-button type="primary" @click="savePermission">Actualizar permisos</el-button>
            </div>
        </template>
    </el-dialog>
</template>

<style scoped>
.boxes {
    height: 25px !important;
}
</style>