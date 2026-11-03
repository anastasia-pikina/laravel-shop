<script setup>
import BreadCrumbs from "../Layers/BreadCrumbs.vue";
import List from "./Products/List.vue";

import {useRoute} from "vue-router";
import {onMounted, ref, watch} from "vue";

const isProduct = ref(false);
const route = useRoute();

onMounted(async () => {
    try {
        let productId = Number(route.params.id);
        if (!isNaN(productId)) {
            isProduct.value = true;
        }
    } catch (error) {
        console.error(error);
    }
});

watch(route, () => fetchNewsByPage(route.params.id));

const fetchNewsByPage = async (id) => {

    if (!id) {
        return;
    }
    let productId = Number(id);
    if (!isNaN(productId)) {
        isProduct.value = true;
    }
    // show.value  = true;
}

</script>

<template>
    <div>
        {{isProduct}}
        <BreadCrumbs />

        <router-view v-if="isProduct" />
        <List v-else />
    </div>
</template>

<style scoped>

</style>
