<template>
  <div class="container mb-4">
    <div class="mx-3">
      <DropDownFilters @sort-item="sortItems" />
    </div>
      <div v-if="downloadStatus === 'isDownloading'" class="text-center">
          <CSpinner class="m-5" color="secondary" visually-hidden-label=""/>
      </div>
      <template v-else>
          <div v-if="grid.cards.length !== 0" class="main-grid d-flex p-3">
              <FilterBar />
              <div class="col-11 col-md-12 col-lg-8 mx-auto" style="margin-left:25px !important">
                  <transition name="fade">
                      <Card :cards="grid.cards" />
                  </transition>
                  <MoreButton v-if="isMoreButtonShow" @click="getProducts" />
              </div>
          </div>
          <Notification v-else class="my-5 py-5">
              <h4>Sorry, we can't find any product with this features</h4>
          </Notification>
      </template>
  </div>
</template>

<script setup>
import {useMainStore} from '../../../store';
import {reactive, onMounted, computed, ref, watch} from 'vue';
import DropDownFilters from './DropDownFilters.vue';
import FilterBar from './FilterBar.vue';
import Card from './Card.vue';
import MoreButton from './MoreButton.vue';
import Notification from '../../Notification.vue';
import Pagination from '../../Pagination/Pagination.vue';
import axios from 'axios';
import { useRoute } from 'vue-router';
import { CSpinner } from '@coreui/bootstrap-vue';

const currentPage = ref(0);
const totalCount = ref(0);
const limit = 2;
const route = useRoute();

const store = useMainStore();

const downloadStatus = ref('notDownload');

const grid = reactive({
  cards: [],
})
onMounted(() => reSet())
//const reSet = () => grid.cards = store.items;
const reSet = async () => {
    downloadStatus.value = 'isDownloading';
    await getProducts();
    downloadStatus.value = 'isDownload';
};

watch(route, () => fetchNewsByPage(route.params.page));
const fetchNewsByPage = async (page) => {
    currentPage.value = page;
   // show.value  = false;
    await getProducts();
   // show.value  = true;
}

const getProducts = async () => {
    currentPage.value++;
    const response = await axios.get(`/api/products?page=${currentPage.value}&limit=${limit}`);

    for (const product of response.data.products) {
        grid.cards.push(product);
    }
    //grid.cards = response.data.products;
    totalCount.value = response.data.count ?? 0;
};

const sortItems = (value) => {
  grid.cards.sort((a, b) => {
    if (value === 'newset') return (a.title.length * 2) - (b.title.length * 4);
    if (value === 'price') return (a.price - b.price);
    if (value === 'trending') return (a.type.length - b.type.length);
  })
  return grid.sortButton = value.toUpperCase()
}

const isMoreButtonShow = computed(() => {
    try {
        return totalCount.value > grid.cards.length;
    } catch (error) {
        console.error(error);
    }

    return false;
});

const pageCount = computed(() => {
    try {
        return Math.ceil(totalCount.value / limit);
    } catch (error) {
        console.error(error);
    }

    return 0;
});

</script>
