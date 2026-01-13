<script setup>
import Header from "./Header.vue";
import '../assets/styles/main.scss'
</script>
<template>
    <div id="app">
        <Header />
        <router-view></router-view>
        <Footer />
    </div>
</template>

<style scoped>
#app {
    background-color: #F8F8F8 !important;
    font-family: 'Exo 2', sans-serif !important;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    color: #2c3e50;
}
</style>
