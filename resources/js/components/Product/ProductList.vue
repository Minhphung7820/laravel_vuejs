<template>
  <div>
    <h2>Product List</h2>
    <button @click="goToCreate">Create New Product</button>
    <ul>
      <li v-for="product in products" :key="product.id">
        <router-link :to="`/products/${product.id}`">{{ product.name }}</router-link>
        - ${{ product.price }}
        <button @click="deleteProduct(product.id)">Delete</button>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      products: []
    };
  },
  created() {
    this.fetchProducts();
  },
  methods: {
    fetchProducts() {
      axios.get('/api/products')
        .then(response => {
          this.products = response.data;
        });
    },
    deleteProduct(id) {
      axios.delete(`/api/products/${id}`)
        .then(() => {
          this.fetchProducts();
        });
    },
    goToCreate() {
      this.$router.push('/products/create');
    }
  }
};
</script>
