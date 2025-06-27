<script setup>
import Layout from './Layout.vue';

const props = defineProps({
    menu: {
        type: Array
    },
});
</script>

<template>
    <Layout :menu="props.menu">

    </Layout>
</template>

<style scoped>

</style>