<template>
  <div>
    <h1 class="pt-3 text-center">New arrivals</h1>
      {{breadcrumbs}}
      <nav class="breadcrumb">
          <ul>
              <li v-for="(crumb, index) in breadcrumbs" :key="index">
                  <router-link :to="crumb.to">{{ crumb.label }}</router-link>
              </li>
          </ul>
      </nav>
    <nav class="d-flex justify-content-center" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link to="/">Home</router-link>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Products</li>
      </ol>
    </nav>
  </div>
</template>

<script setup lang="ts">
import {useRoute} from "vue-router";
import {reactive, onMounted, computed, ref} from 'vue';

const route = useRoute();

const breadcrumbs = computed(() => {
    const matchedRoutes = route.matched;
    console.log(matchedRoutes);
    return matchedRoutes.map((routeItem) => ({
        label: routeItem.meta.breadcrumb || routeItem.name,
        to: getRoutePath(route, routeItem),
    }));
    // const routes = route.matched.map(r => {
    //     return {
    //         text: r.name || 'Unnamed',
    //         route: {name: r.name}
    //     };
    // });
    // console.log(routes)
    // return routes;
});

const getRoutePath = (route, routeItem) => {
    const matchedSegments = route.matched.slice(0, route.matched.indexOf(routeItem) + 1);
    return matchedSegments.map((segment) => segment.path).join('/');
}
</script>

<style scoped lang="scss">
.breadcrumb {
  background: inherit !important;
  color: #2c3539 !important;
  font-size: 20px;

  li {
    text-decoration: none !important;
    color: #f2be00 !important;
  }

  a {
    text-decoration: none !important;
    color: #2c3539 !important;
  }
}
</style>
