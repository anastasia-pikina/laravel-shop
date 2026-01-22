<script setup>

import Rating from "primevue/rating";
import Card from "primevue/card";
import Button from "primevue/button";
import {defineProps, onMounted, ref, computed} from "vue";
import axios from "axios";
import {CSpinner} from "@coreui/bootstrap-vue";

const props = defineProps({
    productId: Number
});

const reviews = ref([]);
const downloadStatus = ref('notDownload');
const currentPage = ref(0);
const totalCount = ref(null);
const limit = 2;

onMounted(async () => {
    downloadStatus.value = 'isDownloading';
    //const response = await axios.get('/api/reviews/' + props.productId);
    const response = await getReviews();
    downloadStatus.value = 'isDownload';
});

const getReviews = async () => {
    currentPage.value++;
    downloadStatus.value = 'isDownloading';
    const response = await axios.get(`/api/reviews`,
        {
            params:
                {
                    page: currentPage.value,
                    limit: limit,
                    product_id: props.productId
                }
        }
    );
    downloadStatus.value = 'isDownload';

    for (const product of response.data.reviews) {
        reviews.value.push(product);
    }

    // reviews.value = response.data.reviews;

    // for (const product of response.data.products) {
    //     grid.cards.push(product);
    // }
    //grid.cards = response.data.products;
    totalCount.value = response.data.count ?? 0;
};

const isMoreButtonShow = computed(() => {
    try {
        console.log(totalCount.value)
        console.log(reviews.value.length)
        return totalCount.value > reviews.value.length;
    } catch (error) {
        console.error(error);
    }

    return false;
});

const revCount = computed(() => {
    try {
        return reviews.value.length;
    } catch (error) {
        console.error(error);
    }

    return false;
});

</script>

<template>
    <div v-if="totalCount === 0">К данному товару пока нет отзывов.</div>
    <div class="product-review" v-for="(review, code) in reviews" :key="code">
        <Card class="mb-3">
            <template #title>Simple Card</template>
            <template #content>
                <Rating v-model="review.rating" readonly/>
                <p class="m-0">
                    {{ review.text }}
                </p>
            </template>
        </Card>
    </div>
    <div v-if="downloadStatus === 'isDownloading'" class="text-center">
        <CSpinner class="m-5" color="secondary" label="Loading..."/>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-3">
        <Button v-if="isMoreButtonShow" label="More" @click="getReviews"/>
    </div>
</template>

<style scoped>

</style>
