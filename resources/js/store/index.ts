import {defineStore} from "pinia";
import {State} from '@/components/types'
import axios from "axios";
import { useRoute } from 'vue-router';

export const useMainStore = defineStore("main", {
    state: (): State => ({
        requestStatus: {
            inProcess: 'inProcess',
            notInProcess: 'notInProcess',
            isSuccess: 'isSuccess',
            isFailed: 'isFailed',
        },
        productInfo: {},
        cartItems: [],
        items: [
            {
                id: 0,
                img: "1.jpg",
                title: "Cabriole",
                price: 156,
                color: "yellow",
                type: "sofa",
            },
            {
                id: 1,
                img: "2.jpg",
                title: "Torchiere",
                price: 756,
                color: "yellow",
                type: "lamp",
            },
            {
                id: 2,
                img: "3.jpg",
                title: "Arm Chair",
                price: 362,
                color: "yellow",
                type: "chair",
            },
            {
                id: 3,
                img: "4.jpg",
                title: "Wing Chair",
                price: 505,
                color: "red",
                type: "chair",
            },
            {
                id: 4,
                img: "5.jpg",
                title: "Camel Back",
                price: 243,
                color: "white",
                type: "sofa",
            },
            {
                id: 5,
                img: "6.jpg",
                title: "Stool",
                price: 44,
                color: "white",
                type: "chair",
            },
            {
                id: 6,
                img: "7.jpg",
                title: "Windsor chair",
                price: 505,
                color: "blue",
                type: "chair",
            },
            {
                id: 7,
                img: "8.jpg",
                title: "C-table",
                price: 432,
                color: "red",
                type: "table",
            },
            {
                id: 8,
                img: "9.jpg",
                title: "Coffee Table",
                price: 390,
                color: "white",
                type: "table",
            },
            {
                id: 9,
                img: "10.jpg",
                title: "Desk Chair",
                price: 756,
                color: "yellow",
                type: "chair",
            },
            {
                id: 10,
                img: "11.jpg",
                title: "Garden Chair",
                price: 44,
                color: "white",
                type: "chair",
            },
            {
                id: 11,
                img: "12.jpg",
                title: "Novelty",
                price: 156,
                color: "red",
                type: "lamp",
            },
            {
                id: 12,
                img: "13.jpg",
                title: "Lava",
                price: 756,
                color: "blue",
                type: "lamp",
            },
            {
                id: 13,
                img: "14.jpg",
                title: "Deck Chair",
                price: 756,
                color: "white",
                type: "chair",
            },
            {
                id: 14,
                img: "table-1.jpg",
                title: "Accent Table",
                price: 756,
                color: "white",
                type: "table",
            },
        ],
    }),
    getters: {
        // Cart Component
        itemsNumber: ({cartItems}): number => cartItems.length,
        totalPrice: ({cartItems}): number | undefined => {
            if (cartItems.length !== 1) {
                const sum = cartItems?.reduce((acc: number, obj: any) => {
                    let result = acc + obj.price
                    return result
                }, 0);
                return sum;
            }
            return cartItems[0].price;
        },
    },
    actions: {
        // Cart Component
        inCart(n: object) {
            return this.cartItems.push(n);
        },
        outCart(n: number) {
            let index = this.cartItems.findIndex((x) => x.id === n);
            return this.cartItems.splice(index, 1);
        },
        // Info Component
        addtoInfo(n: number) {
            const selectedProduct = this.items.find((item) => item.id === n);
            this.productInfo = selectedProduct!;
        },

        getNoun(number, one, two, five) {
            let n = Math.abs(number);
            n %= 100;
            if (n >= 5 && n <= 20) {
                return five;
            }
            n %= 10;

            if (n === 1) {
                return one;
            }

            if (n >= 2 && n <= 4) {
                return two;
            }

            return five;
        },

        async sendRequest(url, method = 'get', data = null) {
            if (!url) {
                return null;
            }

            try {
                let response = null;
                switch (method) {
                    case 'put':
                        response = await axios.put(url, data);
                        break;

                    case 'post':
                        response = await axios.post(url, data);
                        break;

                    default:
                        response = await axios.get(url, data);
                }

                return response.data;
            } catch (error) {
                return null;
            }
        },

        getBreadCrumbs(items) {
            let result = items.map(item => {
                let bread = { label: item.name };
                if (item.link) {
                    bread.route = item.link;
                }
                return bread;
            });
            if (result.length > 1) {
                delete result[result.length - 1].route;
            }
            return result;
        },
    },
});
