<template>
  <div class="navbar-item bc d-none d-xl-block d-lg-block py-0">
    <router-link :to="link.Link" v-for="(link, idx) in navLinks" :id="idx">{{ link.name }}</router-link>
  </div>
</template>

<script setup lang="ts">

const navLinks = [
  {
    name: 'Home',
    Link: '/'
  },
  {
    name: 'Products',
    Link: '/products'
  },
  {
    name: 'Contact',
    Link: '/contact'
  }
];
</script>
<style scoped lang="scss">
.navbar-item {
    a {
        margin-left: 13px;
        font-size: 17px;
        text-decoration: none;
        color: black;
    }
}
</style>
