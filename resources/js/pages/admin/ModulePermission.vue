<script setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';

const props = defineProps({
    module: {
        type: Object,
        required: true
    },
    menu: {
        type: Array
    }
});

onMounted(() => {
    
});
</script>

<template>
    <Layout :menu="props.menu" :module="props.module" :dad="props.module.dad.dad.name">
        Hola mundo 2
    </Layout>
</template>

<style scoped>

</style>