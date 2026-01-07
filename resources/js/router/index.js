import { createRouter, createWebHistory } from 'vue-router';
import ContactForm from '../templates/shop/components/ContactForm.vue';
import Home from "../components/Home.vue";
import ProductsRouter from "../components/ProductsRouter.vue";
import Products from "../components/Products.vue";
import ProductDetail from "../components/ProductDetail.vue";

const routes = [
    {
        path: '/',
        component: Home,
    },
    {
        path: '/products',
        component: ProductsRouter,
        children: [
            {
                path: 'page/:page?',
                component: Products,
                name: 'NewsPage',
            },
            {
                path: '',
                component: Products,
                name: 'News',
            },
            {
                path: ':id/',
                component: ProductDetail,
                name: 'ProductDetail',
            },
        ],
    },
    {
        path: '/contact',
        component: ContactForm,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
