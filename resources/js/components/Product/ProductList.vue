<template>
  <div class="product-list">
    <h2>Product List</h2>
    <button class="create-button" @click="goToCreate">Create New Product</button>
    <ul>
      <li v-for="product in products" :key="product.id" class="product-item">
        <router-link :to="`/products/${product.id}`" class="product-link">{{ product.name }}</router-link>
         {{ product.price }} vnđ
        <button class="delete-button" @click="deleteProduct(product.id)">Delete</button>
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

<style scoped>
.product-list {
  padding: 20px;
  max-width: 600px;
  margin: 0 auto;
  background-color: #f5f5f5;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #333;
  margin-bottom: 20px;
  text-align: center;
}

.create-button {
  background-color: #28a745;
  color: #fff;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  display: block;
  width: 100%;
  font-size: 1.1rem;
  font-weight: bold;
  margin-bottom: 20px;
}

.create-button:hover {
  background-color: #218838;
}

ul {
  list-style-type: none;
  padding: 0;
}

.product-item {
  padding: 10px;
  margin-bottom: 10px;
  border-bottom: 1px solid #ddd;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-link {
  font-size: 1rem;
  color: #3498db;
  text-decoration: none;
}

.product-link:hover {
  text-decoration: underline;
}

.delete-button {
  background-color: #e74c3c;
  color: #fff;
  padding: 5px 10px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.delete-button:hover {
  background-color: #c0392b;
}
</style>
