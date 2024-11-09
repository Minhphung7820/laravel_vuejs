<template>
  <div>
    <h2>{{ isEditMode ? 'Edit' : 'Create' }} Product</h2>
    <form @submit.prevent="submitForm">
      <div>
        <label>Name:</label>
        <input v-model="product.name" required />
      </div>
      <div>
        <label>Description:</label>
        <textarea v-model="product.description"></textarea>
      </div>
      <div>
        <label>Price:</label>
        <input type="number" v-model="product.price" required />
      </div>
      <div>
        <label>Quantity:</label>
        <input type="number" v-model="product.quantity" required />
      </div>
      <button type="submit">{{ isEditMode ? 'Update' : 'Create' }}</button>
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
        price: '',
        quantity: ''
      },
      isEditMode: false
    };
  },
  created() {
    if (this.$route.params.id) {
      this.isEditMode = true;
      this.fetchProduct();
    }
  },
  methods: {
    fetchProduct() {
      axios.get(`/api/products/${this.$route.params.id}`)
        .then(response => {
          this.product = response.data;
        });
    },
    submitForm() {
      const request = this.isEditMode
        ? axios.put(`/api/products/${this.$route.params.id}`, this.product)
        : axios.post('/api/products', this.product);

      request.then(() => {
        this.$router.push('/products');
      });
    }
  }
};
</script>
