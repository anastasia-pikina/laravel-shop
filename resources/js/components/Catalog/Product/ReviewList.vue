<script setup>

import Rating from "primevue/rating";
import Card from "primevue/card";
import Button from "primevue/button";
import {defineProps, onMounted, ref, computed} from "vue";
import axios from "axios";
import {useMainStore} from "../../../store/index.js";
import ProgressSpinner from 'primevue/progressspinner';

const props = defineProps({
    productId: Number
});

const store = useMainStore();
const reviews = ref([]);
const downloadStatus = ref(null);
const currentPage = ref(0);
const totalCount = ref(null);
const limit = 2;

onMounted(async () => {
    await getReviews();
});

const getReviews = async () => {
    try {
        currentPage.value++;
        downloadStatus.value = store.requestStatus.inProcess;

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

        downloadStatus.value = store.requestStatus.isSuccess;
        const dateOptions = {
            year: "numeric",
            month: "numeric",
            day: "numeric",
            hour: "numeric",
            minute: "numeric",
            second: "numeric",
            hour12: false,
            timeZone: "Europe/Moscow",
        };

        for (const review of response.data.reviews) {
            const date = new Date(review.created_at);
            review.created_at = new Intl.DateTimeFormat("ru", dateOptions).format(date);
            reviews.value.push(review);
        }

        totalCount.value = response.data.count ?? 0;
    } catch (error) {
        console.error(error);
    }
};

const isMoreButtonShow = computed(() => {
    try {
        return totalCount.value > reviews.value.length;
    } catch (error) {
        console.error(error);
    }

    return false;
});

const requestInProcess = computed(() => {
    try {
        return downloadStatus.value === store.requestStatus.inProcess;
    } catch (error) {
        console.error(error);
    }

    return false;
});

const isEmptyReviews = computed(() => {
    return totalCount.value === 0;
});
</script>

<template>
    <div v-if="isEmptyReviews">К данному товару пока нет отзывов.</div>
    <div class="product-review" v-for="(review, code) in reviews" :key="code">
        <Card class="mb-3">
            <template #title>Simple Card</template>
            <template #subtitle>{{ review.created_at }}</template>
            <template #content>
                <Rating v-model="review.rating" readonly/>
                <div class="mt-3">
                    {{ review.text }}
                </div>
            </template>
        </Card>
    </div>
    <ProgressSpinner class="spinner mt-3 mb-3" strokeWidth="4" fill="transparent" v-if="requestInProcess" />
    <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-3">
        <Button v-if="isMoreButtonShow" label="Еще" @click="getReviews"/>
    </div>
</template>

<style scoped>
.spinner {
    width: 50px;
    height: 50px;
}
</style>
