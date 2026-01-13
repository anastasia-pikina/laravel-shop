<template>
  <div>
    <nav class="row justify-content-start" aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <router-link to="/">Home</router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link to="/products">Products</router-link>
        </li>
        <li class="breadcrumb-item">
          <router-link to="/products">{{ details.type }}</router-link>
        </li>
        <li class="breadcrumb-item active">{{ details.name }}</li>
      </ol>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { Product } from '../types'

defineProps<{
  details: Product
}>()

</script>

<style scoped lang="scss">
.breadcrumb {
  background: inherit;

  li {
    a {
      text-decoration: none;
      color: #2c3539 !important;
      font-size: 18px;

      &:hover {
        text-decoration: underline;
      }
    }
  }

  .active {
    text-decoration: none !important;
    color: #f2be00 !important;
  }
}
</style>
