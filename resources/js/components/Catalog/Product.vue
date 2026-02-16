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
import {reactive, onMounted, computed, ref} from 'vue';
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

const router = useRoute();

onMounted(async () => {
    breadItems.value = [];
    downloadStatus.value = 'isDownloading';
    let itemId = Number(route.params.id)
    const response = await axios.get('/api/products/' + itemId);
    downloadStatus.value = 'isDownload'
    item.details = response.data.product;
    reviews.value.count = response.data.reviews_count;
    reviews.value.average_rating = response.data.reviews_average_rating;

    breadItems.value = store.getBreadCrumbs(router, {
        '#product_name#': item.details.name,
        '#category_link#': item.details.category_id,
        '#category_name#': item.details.category.name,
    });
})

const sliceItems = computed(() => {
    for (let i = 0; i < 3; i++) {
        const randomIndex = Math.floor(Math.random() * store.items.length)
        item.relatedItems.push(store.items[randomIndex])
    }
    return item.relatedItems
})
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
