<script lang="js" setup>
import Layout from './Layout.vue';

const { menu } = defineProps({
    menu: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <Layout :menu="menu">

    </Layout>
</template>

<style scoped>

</style>