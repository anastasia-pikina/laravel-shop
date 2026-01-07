<template>
    <CCarousel controls dark indicators transition="crossfade">
        <CCarouselItem>
            <img class="d-block w-100" src="../../assets/jum1.jpg" alt="slide 1"/>
            <CCarouselCaption class="d-md-block">
                <h5>First slide label</h5>
                <p>Some representative placeholder content for the first slide.</p>
            </CCarouselCaption>
        </CCarouselItem>
        <CCarouselItem>
            <img class="d-block w-100" src="../../assets/jum2.jpg" alt="slide 2"/>
        </CCarouselItem>
        <CCarouselItem>
            <img class="d-block w-100" src="../../assets/jum3.jpg" alt="slide 3"/>
        </CCarouselItem>
    </CCarousel>
</template>
<script setup>
import { CCarousel } from '@coreui/bootstrap-vue'
import { CCarouselItem } from '@coreui/bootstrap-vue'
import { CCarouselCaption } from '@coreui/bootstrap-vue'
import 'bootstrap/dist/css/bootstrap.min.css'
</script>
<style scoped>
.img-fluid {
  min-width: 800px !important;
}

.carousel-indicators button {
  background-color: white;
  height: 20px;
  width: 20px;
  border-radius: 30px;
  border: 0.6px solid rgb(129, 127, 127);
}

.carousel-indicators>.active {
  background: #e0f0f8;
}
</style>
