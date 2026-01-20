<script setup>

import Rating from "primevue/rating";
import Card from "primevue/card";
import Button from "primevue/button";
import {defineProps, onMounted, ref} from "vue";
import axios from "axios";
import {CSpinner} from "@coreui/bootstrap-vue";

const props = defineProps({
    productId: Number
});

const reviews = ref([]);
const downloadStatus = ref('notDownload');
const currentPage = ref(0);
const totalCount = ref(0);
const limit = 2;

onMounted(async () => {
    downloadStatus.value = 'isDownloading';
    const response = await axios.get('/api/reviews/' + props.productId);
    downloadStatus.value = 'isDownload';
    reviews.value = response.data;
});
</script>

<template>
    <div v-if="downloadStatus === 'isDownloading'" class="text-center">
        <CSpinner class="m-5" color="secondary" label="Loading..."/>
    </div>
    <div v-else class="product-review" v-for="(review, code) in reviews" :key="code">
        <Card class="mb-3">
            <template #title>Simple Card</template>
            <template #content>
                <Rating v-model="review.rating" readonly />
                <p class="m-0">
                    {{ review.text }}
                </p>
            </template>
        </Card>
    </div>
    <div class="d-grid gap-2 d-md-flex justify-content-md-center mb-3">
        <Button label="More" />
    </div>
</template>

<style scoped>

</style>
