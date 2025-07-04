<script lang="js" setup>
import Layout from './Layout.vue';

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
</script>

<template>
    <Layout :menu="menu" :module="module">
        
    </Layout>
</template>

<style scoped>

</style>