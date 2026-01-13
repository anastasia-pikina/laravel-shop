<script setup>
import Header from "./Layers/Header.vue";
import Footer from "./Layers/Footer.vue";
import '../assets/styles/main.scss'
</script>
<template>
    <div id="app">
        <Header />
        <transition name="fade">
          <router-view></router-view>
        </transition>
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
