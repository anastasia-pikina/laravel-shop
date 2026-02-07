<script setup>

import BreadCrumbs from "../Layers/BreadCrumbs.vue";
import {useRoute} from "vue-router";

const router = useRoute();
</script>

<template>
    <transition name="fade">
        <router-view />
    </transition>
</template>

<style scoped>
</style>
