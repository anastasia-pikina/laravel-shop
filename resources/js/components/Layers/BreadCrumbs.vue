<template>
    <Breadcrumb :home="home" :model="items">
        <template #item="{ item, props }">
            <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                <a :href="href" v-bind="props.action" @click="navigate">
                    <span :class="[item.icon, 'text-color']" />
                    <span class="text-primary font-semibold">{{ item.label }}</span>
                </a>
            </router-link>
            <a v-else :href="item.url" :target="item.target" v-bind="props.action">
                <span class="text-surface-950 dark:text-surface-0">{{ item.label }}</span>
            </a>
        </template>
    </Breadcrumb>
</template>

<script setup lang="ts">
import {useRoute} from "vue-router";
import {ref, defineProps} from 'vue';
import Breadcrumb from 'primevue/breadcrumb';

const home = ref({
    icon: 'pi pi-home',
    route: '/'
});

const props = defineProps({
    items: []
})
</script>
