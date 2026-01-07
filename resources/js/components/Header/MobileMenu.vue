<template>
    <div class="dropdown d-xl-none d-lg-none">
        <img @click="showMenu" src="https://img.icons8.com/office/35/menu--v1.png" width="28" height="28" data-bs-toggle="dropdown"
            data-bs-target="#navd" aria-haspopup="true" aria-expanded="false" alt="mobile-menu" title="mobile-menu">
        <div class="dropdown-menu hb" :class="{'show' : isShow}" aria-labelledby="navd">
            <router-link class="dropdown-item" to="/">Home</router-link>
            <router-link class="dropdown-item" to="/products">Products</router-link>
            <router-link class="dropdown-item" to="/contact">Contact us</router-link>
        </div>
    </div>
</template>
<script setup>
import { ref } from 'vue';
const isShow = ref(false);
const showMenu = () => {
    isShow.value = isShow.value === false;
}
</script>
