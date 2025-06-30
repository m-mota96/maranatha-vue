<script lang="ts" setup>
import Layout from './Layout.vue';

const { menu } = defineProps<{
    menu: []
}>();
</script>

<template>
    <Layout :menu="menu">

    </Layout>
</template>

<style scoped>

</style>