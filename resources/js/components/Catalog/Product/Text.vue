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
        <p>{{item.description}}</p>
      </div>
    </div>
    <div class="container pt-3" v-if="isShowReviews">
      <div class="row">
          <div v-if="downloadStatus === 'isDownloading'" class="text-center">
            <CSpinner class="m-5" color="secondary" label="Loading..."/>
          </div>
          <div v-else class="product-review" v-for="(review, code) in reviews" :key="code">
              {{review.text}}
          </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import {onMounted, reactive, computed, defineProps, ref} from 'vue';
import { CSpinner } from '@coreui/bootstrap-vue';
import {Product} from "../../types";
const route = useRoute()
import axios from "axios";
import {useRoute} from "vue-router";

const text = reactive({
  moreInfo: ['DESCRIPTION', 'REVIEWS'],
  active: 0,
});

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
    downloadStatus.value = 'isDownloading';
    let itemId = Number(route.params.id)
    const response = await axios.get('/api/reviews/' + itemId);
    downloadStatus.value = 'isDownload';
    reviews.value = response.data;
});

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
</style>
