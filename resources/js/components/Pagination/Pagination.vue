<script setup>
import { computed} from 'vue';

import PaginationPage from './PaginationPage.vue';


const props = defineProps([
  'currentPage',
  'pageCount',
  'visiblePagesCount'
]);


const emit = defineEmits(['changePage'])

const paginationPages = computed(() => {

  let resultPages = {};

  try {
    const pageCount = props.pageCount;
    resultPages[1] = 1;
    if (pageCount === 1) {
      return resultPages;
    }

    const currentPage = props.currentPage;
    let visiblePagesCount = props.visiblePagesCount;
    const visiblePagesThreshold = (visiblePagesCount - 1) / 2;

    visiblePagesCount--;

    if (currentPage <= visiblePagesThreshold + 1 || pageCount <= props.visiblePagesCount + 1) {
      let lastPage = 1;

      while (visiblePagesCount >= 1) {
        resultPages[++lastPage] = lastPage;
        visiblePagesCount--;
        if (Object.keys(resultPages).length === pageCount) {
          return resultPages;
        }
      }

      resultPages[++lastPage] = '...';
      resultPages[pageCount] = pageCount;
      return resultPages;
    }

    if (currentPage >= pageCount - visiblePagesThreshold + 1) {
      let lastPage = pageCount - visiblePagesCount -1;
      resultPages[lastPage] = '...';

      while (visiblePagesCount >= 1) {
        resultPages[++lastPage] = lastPage;
        visiblePagesCount--;
      }

      resultPages[pageCount] = pageCount;
      return resultPages;
    }

    let lastPage = currentPage - visiblePagesThreshold;
    if (pageCount - currentPage === 2) {
      lastPage--;
    }
    resultPages[lastPage] = lastPage === 2 ? lastPage : '...';

    while (visiblePagesCount > 1) {
      resultPages[++lastPage] = lastPage;
      visiblePagesCount--;
    }

    resultPages[++lastPage] = (lastPage === pageCount - 1) ? lastPage : '...';
    resultPages[pageCount] = pageCount;
  } catch (e) {
    console.error(e.message);
  }

  return resultPages;
});

const pagePrevLink = computed(() => {
    try {
      let prevPage = props.currentPage - 1;
      if (prevPage < 1) {
        prevPage = 1;
      }

     // return store.getNewListUrl(prevPage);
    } catch (e) {
      console.error(e.message);
    }

    return {};
});

const pageNextLink = computed(() => {
  try {
    let currentPage = props.currentPage;
    if (currentPage === 0) {
      currentPage = 1;
    }

    let nextPage = Number(currentPage) + 1;

    if (nextPage > props.pageCount) {
      nextPage = props.pageCount;
    }

    return store.getNewListUrl(nextPage);
  } catch (e) {
    console.error(e.message);
  }

  return {};
});

</script>

<template>
  <div class="pagination">
    <router-link :to="pagePrevLink"
                 :class="{
                  'disabled':
                  1 === Number(props.currentPage)
                }"
    >
      предыдущая
    </router-link>
    <PaginationPage
        v-for="(pageNumberView, pageNumber) in paginationPages" :key="pageNumber"
        :class="{
              'page-current':
              Number(pageNumber) === Number(props.currentPage)
            }"
        :pageNumber="pageNumber"
        :pageNumberView="pageNumberView"
        class="page"
    />
    <router-link :to="pageNextLink"
                 :class="{
                  'disabled':
                  Number(props.pageCount) === Number(props.currentPage)
                }"
    >
      следующая
    </router-link>
  </div>
</template>

<style scoped>
  .pagination {
    padding: 0 30px;
    text-transform: uppercase;
    font-size: 12px;
    text-align: center;
  }

  a.disabled
  {
    color: #5c6470;
    cursor: default;
  }
</style>
