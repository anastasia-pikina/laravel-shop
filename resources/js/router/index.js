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
            breadcrumb: [
                { name: 'Home' }
            ]
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
                meta: {
                    breadcrumb: [
                        { name: 'Каталог' }
                    ]
                }
            },
            {
                path: ':category?',
                component: Products,
                name: 'Products',
                meta: {
                    breadcrumb: [
                        { name: '#category_name#' }
                    ]
                }
            },
            {
                path: ':category/:id/',
                component: Product,
                name: 'Product',
                meta: {
                    breadcrumb: [
                        { name: '#category_name#', link: '/products/#category_link#/' },
                        { name: '#product_name#' }
                    ]
                }
            },
        ],
        name: 'CatalogRouter',
        meta: {
            // Can be an object
            breadcrumb: [
                {name: 'Каталог'},
            ],
        },
    },
    {
        path: '/contact',
        component: Contact,
        meta: {
            breadcrumb: [
                { name: 'Контакты' }
            ]
        },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
