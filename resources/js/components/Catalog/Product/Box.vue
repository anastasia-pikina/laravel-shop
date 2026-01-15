<template>
  <div>
    <div class="row mb-5">
      <div class="col6 col-xl-6 col-lg-6 col-md-12 col-sm-12">
          <CPlaceholder v-if="isLoading" component="div" animation="glow" style="aspect-ratio: 1/1; width: 100%;">
              <CPlaceholder component="div" size="lg" style="height: 100%; width: 100%;" />
          </CPlaceholder>
        <img v-else class="img-fluid" :src="item.image">
      </div>

      <div class="col6 col-xl-6 col-lg-6 col-md-12 col-sm-12 d-flex align-items-center justify-content-start">
        <div class="info pt-xl-0 pt-lg-0 pt-5" style="min-width: 250px;">
            <CCardTitle v-c-placeholder="{animation: 'glow', xs: 12}">
                <CPlaceholder :xs="12" />
            </CCardTitle>
            <CPlaceholder  component="p" animation="wave">
                <CPlaceholder :xs="12" size="lg" style="height: 100px;" />
            </CPlaceholder>
            <span class="pr-3">★★★★★</span>
            <CPlaceholder  component="p" animation="wave">
                <CPlaceholder :xs="12" />
            </CPlaceholder>
            <h6  style="width:190px;">3 reviews</h6>
            <CPlaceholder component="p" animation="wave">
                <CPlaceholder :xs="12" />
            </CPlaceholder>
            <h1 class="font-weight-bold text-uppercase pt-3">{{ item.name }}</h1>
            <CPlaceholder component="p" animation="wave">
                <CPlaceholder :xs="12" />
            </CPlaceholder>
          <h4>{{ item.price }} руб</h4>
          <br><br><br>
            <CPlaceholder component="p" animation="wave">
                <CPlaceholder :xs="12" />
            </CPlaceholder>
          <div class="control number text-center">
            <button class="decrement-button" @click="decrememnt"
              style="border-right: 0.2px solid lightgrey;float:left;margin-right: 11px;">−</button>
            <span>{{ quantity }}</span>
            <button class="increment-button" @click="incrememnt"
              style="border-left: 0.2px solid lightgrey;margin-left: 16px;">+</button>
            <br><br>
          </div>
            <CPlaceholder component="p" animation="wave">
                <CPlaceholder :xs="12" />
            </CPlaceholder>
          <button class="add-to-cart-button" @click="addtoCart(item)">В корзину</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Product } from '../../types'
import {useMainStore} from '../../../store';
import { ref } from 'vue';
import { CPlaceholder, CCardImage, CCardTitle } from '@coreui/bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.min.css'
import '@coreui/coreui/dist/css/coreui.css'
import { CButton } from '@coreui/bootstrap-vue';

const store = useMainStore();

defineProps<{
  item: Product,
    isLoading: boolean,
}>()

const quantity = ref(1)

const incrememnt = () => quantity.value < 9 ? quantity.value++ : 0
const decrememnt = () => quantity.value > 1 ? quantity.value-- : 0

function addtoCart(item: object) {
  for (var i = 0; i < quantity.value; i++) {
    store.inCart(item)
  }
}
</script>

<style scoped lang="scss">
.img-fluid {
  min-width: 100% !important;
  box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
}

.control {
  &.number {
    border: 0.2px solid lightgrey;
    font-size: 19px;
    font-weight: bold;
    height: 35px;
    width: 155px;
    margin-bottom: 30px;

    button {
      border: none;
      background: inherit;
      width: 56px;
      height: 35px;
      outline-style: none;

      &:active {
        background-color: lightgrey;
      }

      h5 {
        margin-left: 13px;
        margin-right: 13px;
      }
    }
  }
}

.add-to-cart-button {
  -webkit-transition-duration: 500ms;
  transition-duration: 500ms;
  width: 155px;
  background-color: #2c3539;
  color: #fff;
  font-size: 15px;
  padding: 10px 30px;
  cursor: pointer;
  position: relative;
  top: 0;
  z-index: 10;
  border: none;
  box-shadow: 0 26px 38px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);

  &:hover,
  &:focus {
    background-color: inherit;
    color: black;
  }
}
</style>
