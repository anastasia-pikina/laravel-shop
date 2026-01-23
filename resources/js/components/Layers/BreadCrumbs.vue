<template>
    {{breadcrumbs}}
    <Breadcrumb :home="home" :model="items">
        <template #item="{ item, props }">
            <router-link v-if="item.route" v-slot="{ href, navigate }" :to="item.route" custom>
                <a :href="href" v-bind="props.action" @click="navigate">
                    <span :class="[item.icon, 'text-color']" />
                    <span class="text-primary font-semibold">{{ item.label }}</span>
                </a>
            </router-link>
            <a v-else :href="item.url" :target="item.target" v-bind="props.action">
                <span class="text-surface-700 dark:text-surface-0">{{ item.label }}</span>
            </a>
        </template>
    </Breadcrumb>
</template>

<script setup lang="ts">
import {useRoute} from "vue-router";
import {reactive, onMounted, computed, ref} from 'vue';
import Breadcrumb from 'primevue/breadcrumb';

const home = ref({
    icon: 'pi pi-home',
    route: '/'
});
// const items = ref([
//     { label: 'Components' },
//     { label: 'Form' },
//     { label: 'InputText', route: '/inputtext' }
// ]);

const items = ref([]);

const router = useRoute();

const breadcrumbs = computed(() => {
    console.log(router)
    const linkCount = router.matched.length;
    let i = 0;
    for (const t of router.matched) {
        i++;
        let routerData = {label: t.meta.breadcrumb.label};
        if (i < linkCount) {
            routerData.route = t.path;
        }
        items.value.push(routerData);
    }
    // let activedRoutes = [];
    // router.beforeEach((to, from, next) => {
    //    // activedRoutes = [];
    //     to.matched.forEach((record) => { activedRoutes.push(record) })
    //     next();
    // });

    //return activedRoutes;
    // const matchedRoutes = route.matched;
    // console.log(matchedRoutes);
    // return matchedRoutes.map((routeItem) => ({
    //     label: routeItem.meta.breadcrumb || routeItem.name,
    //     to: getRoutePath(route, routeItem),
    // }));
    // const routes = route.matched.map(r => {
    //     return {
    //         text: r.name || 'Unnamed',
    //         route: {name: r.name}
    //     };
    // });
    // console.log(routes)
    // return routes;
});

const getRoutePath = (route, routeItem) => {
    const matchedSegments = route.matched.slice(0, route.matched.indexOf(routeItem) + 1);
    return matchedSegments.map((segment) => segment.path).join('/');
}
</script>

<style scoped lang="scss">
.breadcrumb {
  background: inherit !important;
  color: #2c3539 !important;
  font-size: 20px;

  li {
    text-decoration: none !important;
    color: #f2be00 !important;
  }

  a {
    text-decoration: none !important;
    color: #2c3539 !important;
  }
}
</style>
