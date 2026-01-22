<template>
    <div>
        <div class="more info d-flex justify-content-between text-center">
            <div class="col4 flex-fill" v-for="(info, index) in text.moreInfo"
                 :class="[index === text.active ? 'col4 active' : '']" @click="selectedInfo(index)" :key="index">
                <h6>{{ info }}</h6>
            </div>
        </div>
        <div class="container pt-3" v-if="isShowDescription">
            <div class="row">
                <p>{{ item.description }}</p>
            </div>
        </div>
        <div class="container pt-3" v-if="isShowReviews">
            <div class="row">
                <ReviewList :productId="props.item.id" />
                <ReviewAdd :productId="item.id" />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import {onMounted, reactive, computed, defineProps, ref} from 'vue';
import {CSpinner} from '@coreui/bootstrap-vue';
import {Product} from "../../types";
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import axios from "axios";
import {useRoute} from "vue-router";
import {CButton} from '@coreui/bootstrap-vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Drawer from 'primevue/drawer';
import {Form} from '@primevue/forms';
import {FormField} from '@primevue/forms';
import 'primeicons/primeicons.css';
import Rating from 'primevue/rating';
import ReviewAdd from './ReviewAdd.vue';
import ReviewList from './ReviewList.vue';
import Card from 'primevue/card';

const route = useRoute()

const text = reactive({
    moreInfo: ['DESCRIPTION', 'REVIEWS'],
    active: 0,
});

const isAddReview = ref(false);

const visible = ref(false);

const reviews = ref([]);
const downloadStatus = ref('notDownload');

// defineProps<{
//     item: Product
// }>()

const props = defineProps({
    item: Object
})

const isShowDescription = computed(() => {
    return text.active === 0;
});

const isShowReviews = computed(() => {
    return text.active === 1;
});

//More Info area tab selector
const selectedInfo = (index: number): number => text.active = index;

onMounted(async () => {
  //   downloadStatus.value = 'isDownloading';
  //   let itemId = Number(route.params.id)
  // //  const response = await axios.get('/api/reviews/' + itemId);
  //   downloadStatus.value = 'isDownload';
  //   reviews.value = response.data;
});

// const isAddReviewMode = () => {
//     isAddReview.value = true;
// }

</script>

<style scoped>
.col4 h6 {
    height: 25px;
    padding-bottom: 25px;
    border-bottom: 1px solid lightgrey;
    color: grey;
    cursor: pointer;
}

.col4.active h6 {
    font-weight: bold;
    border-bottom: 1px solid black !important;
    color: black;
}

.star {
    cursor: pointer;

    &:hover {
        &:before {
            content: "\e936";
        }
    }
}
</style>
