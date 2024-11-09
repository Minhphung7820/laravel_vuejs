<template>
  <div>
    <h2>{{ product.name }}</h2>
    <p>{{ product.description }}</p>
    <p>Price: ${{ product.price }}</p>
    <p>Quantity: {{ product.quantity }}</p>
    <button @click="goToEdit">Edit</button>
    <button @click="goBack">Back to List</button>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      product: {}
    };
  },
  created() {
    this.fetchProduct();
  },
  methods: {
    fetchProduct() {
      axios.get(`/api/products/${this.$route.params.id}`)
        .then(response => {
          this.product = response.data;
        });
    },
    goToEdit() {
      this.$router.push(`/products/${this.product.id}/edit`);
    },
    goBack() {
      this.$router.push('/products');
    }
  }
};
</script>
