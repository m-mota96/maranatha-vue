<script lang="js" setup>
import { ref, onMounted } from 'vue';
import Layout from './Layout.vue';
import { dateEs } from '@/dateEs';
import { format as formatDates } from 'date-fns';

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

const search = ref({
    year: new Date().getFullYear(),
    month: new Date().getMonth() + 1,
    currentDate: new Date().toLocaleDateString('en-CA')
});

onMounted(() => {
    console.log(search.value);
});

</script>

<template>
    <Layout :menu="menu" :module="module">
        
    </Layout>
</template>