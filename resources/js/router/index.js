import { createRouter, createWebHistory } from 'vue-router';
import Contact from '../components/Contact.vue';
import Home from "../components/Home.vue";
import CatalogRouter from "../components/Catalog/CatalogRouter.vue";
import Products from "../components/Catalog/Products.vue";
import Product from "../components/Catalog/Product.vue";

const routes = [
    {
        path: '/',
        component: Home,
        meta: {
            breadcrumb: 'Home' // Can be just a string
        },
    },
    {
        path: '/products',
        component: CatalogRouter,
        children: [
            {
                path: 'page/:page?',
                component: Products,
                name: 'ProductsPage',
            },
            {
                path: ':category?',
                component: Products,
                name: 'Products',
            },
            {
                path: ':category/:id/',
                component: Product,
                name: 'Product',
            },
        ],
        meta: {
            // Can be an object
            breadcrumb: {
                label: 'Каталог',
                link: '/products' // custom link
            },
        }
    },
    {
        path: '/contact',
        component: Contact,
        meta: {
            // Can be an object
            breadcrumb: {
                label: 'Контакты',
                link: '/contact' // custom link
            },
        }
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
