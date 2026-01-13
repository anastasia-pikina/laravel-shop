<template>
    <div>
        <h1>Create Product</h1>
        <form @submit.prevent="createProduct">
            <label>Product Name:</label>
            <input v-model="product.name" type="text" required />

            <label>Description:</label>
            <textarea v-model="product.description"></textarea>

            <label>Price (₽):</label>
            <input v-model="product.price" type="number" step="0.01" required />
            <button type="submit" class="create-btn">Create</button>
            <router-link to="/" class="cancel-btn">Cancel</router-link>
        </form>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            product: {
                name: '',
                description: '',
                price: 0,
            },
        };
    },
    methods: {
        async createProduct() {
            await axios.post('/api/products', this.product);
            this.$router.push('/');
        },
    },
};
</script>

<style scoped>
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
input, textarea {
    width: 100%;
    padding: 8px;
}
button {
    margin-top: 10px;
    padding: 8px 12px;
    cursor: pointer;
}
.create-btn {
    background-color: green;
    color: white;
}
.cancel-btn {
    background-color: gray;
    color: white;
    text-decoration: none;
    display: inline-block;
    padding: 8px 12px;
    text-align: center;
}
</style>
