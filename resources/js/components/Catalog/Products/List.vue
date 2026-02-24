<template>
    <BreadCrumbs :items="breadItems" />
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
              <div class="col-11 col-md-12 col-lg-8 mx-auto product-list">
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
import axios from 'axios';
import { useRoute } from 'vue-router';
import { CSpinner } from '@coreui/bootstrap-vue';
import BreadCrumbs from "../../Layers/BreadCrumbs.vue";

const currentPage = ref(0);
const totalCount = ref(0);
const limit = 3;
const route = useRoute();

const store = useMainStore();

const downloadStatus = ref('notDownload');

const grid = reactive({
  cards: [],
});
const breadItems = ref([]);

const router = useRoute();

onMounted( () => {
    console.log('onMounted')
    grid.cards = [];
    reSet();
});
//onMounted(() => reSet());

//const reSet = () => grid.cards = store.items;
const reSet = async () => {
    downloadStatus.value = 'isDownloading';
    await getProducts();
    downloadStatus.value = 'isDownload';
};

watch(route, () => fetchNewsByPage(route.params.page));
watch(route, () => fetchNewsCategory(route.params.category));

const fetchNewsCategory = async (category) => {
    grid.cards = [];
    currentPage.value = 0;
    await getProducts();
}
const fetchNewsByPage = async (page) => {
    if (!page) {
        return;
    }
    currentPage.value = page;
   // show.value  = false;
    await getProducts();
   // show.value  = true;
}

const getProducts = async () => {
    currentPage.value++;
    let categoryId = Number(route.params.category);
    const response = await axios.get(`/shop/products`,
        {
            params:
                {
                    page: currentPage.value,
                    limit: limit,
                    categoryId: categoryId,
                }
        }
    );

    for (const product of response.data.products) {
        grid.cards.push(product);
    }

    let category_name = '';
    if (response.data.category) {
        category_name = response.data.category.name;
    }

    breadItems.value = store.getBreadCrumbs(router, {
        '#category_name#': category_name,
    });

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

<style scoped>
    .product-list {
        margin-left:25px;
    }
</style>
