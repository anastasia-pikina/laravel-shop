<template >
    <div class="container py-5" style="padding-top:70px;">
        <!-- TODO dont allow accessing of the route to this page '/info' except if there is info to display -->
        <Breadcrumb :details="(item.details)" />
        <Box :item="item.details" />
        <Text />

        <div class="related-item">
            <hr>
            <h6 class="pb-4">RELATED PRODUCTS</h6>
                <Card :cards="sliceItems" />
        </div>

    </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router';
import { reactive, onMounted, computed } from 'vue';
import { Product } from './types';
import Breadcrumb from "./Details/Breadcrumb.vue";
import Box from "./Details/Box.vue";
import Text from "./Details/Text.vue";
import Card from "./Products/Card.vue";

import {useMainStore} from '../store/index';
import axios from "axios";
const store = useMainStore()
const route = useRoute()

interface Item {
    details: Product
    relatedItems: Product[]
}

const item: Item = reactive({
    details: {},
    relatedItems: []
})

onMounted(async () => {
    let itemId = Number(route.params.id)
    const response = await axios.get('/api/products/' + itemId);
    item.details = response.data;
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
