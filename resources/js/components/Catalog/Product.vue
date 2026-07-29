<template >
    <BreadCrumbs :items="breadItems" />
    <div class="container py-5" style="padding-top:70px;">
        <!-- TODO dont allow accessing of the route to this page '/info' except if there is info to display -->
        <Box :item="item.details" :reviews="reviews" :isLoading="downloadStatus === 'isDownloading'" />
        <Text :item="item.details" />

        <div class="related-item">
            <hr>
            <h6 class="pb-4">RELATED PRODUCTS</h6>
                <Card :cards="sliceItems" />
        </div>

    </div>
</template>

<script setup lang="ts">

import { useRoute } from 'vue-router';
import {reactive, onMounted, computed, ref, watch} from 'vue';
import { Product } from '../types';
import BreadCrumbs from "../Layers/BreadCrumbs.vue";
import Box from "./Product/Box.vue";
import Text from "./Product/Text.vue";
import Card from "./Products/Card.vue";

import {useMainStore} from '../../store';
import axios from "axios";
const store = useMainStore()
const route = useRoute();

const downloadStatus = ref('notDownload');
interface Item {
    details: Product
    relatedItems: Product[],
}

const item: Item = reactive({
    details: {},
    relatedItems: []
});

const reviews = ref({
    count: 0,
    average_rating: 0,
});

const breadItems = ref([]);

const updateBreadcrumbs = () => {
    let items = [{ name: 'Каталог', link: '/products' }];
    let cat = item.details.category;
    if (cat) {
        let parentPath = '';
        if (cat.parent) {
            parentPath = cat.parent.code + '/';
            items.push({ name: cat.parent.name, link: '/products/' + parentPath });
        }
        items.push({ name: cat.name, link: '/products/' + parentPath + cat.code + '/' });
    }
    items.push({ name: item.details.name });
    breadItems.value = store.getBreadCrumbs(items);
};

const fetchProduct = async (id) => {
    breadItems.value = [];
    downloadStatus.value = 'isDownloading';
    const response = await axios.get('/shop/products/' + id);
    downloadStatus.value = 'isDownload';
    item.details = response.data.product;
    item.relatedItems = response.data.recommended || [];
    reviews.value.count = response.data.reviews_count;
    reviews.value.average_rating = response.data.reviews_average_rating;
    updateBreadcrumbs();
};

onMounted(() => fetchProduct(Number(route.params.id)));

watch(() => route.params.id, (newId) => {
    if (newId) fetchProduct(Number(newId));
});

const sliceItems = computed(() => item.relatedItems)
</script>

<style scoped>
hr {
    width: 50px;
    border-bottom: 1px solid black;
}

.related-item {
    padding-left: 8rem;
    padding-right: 8rem;
    height: auto;
    text-align: center;
}
</style>
