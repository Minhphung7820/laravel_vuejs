<template>
  <div class="product-form">
    <h2>{{ isEditMode ? 'Edit' : 'Create' }} Product</h2>
    <form @submit.prevent="submitForm">
      <div class="form-group">
        <label>Name:</label>
        <input v-model="product.name" required />
      </div>
      <div class="form-group">
        <label>Description:</label>
        <textarea v-model="product.description"></textarea>
      </div>
      <div class="form-group">
        <label>Price:</label>
        <input type="number" v-model="product.price" required />
      </div>
      <div class="form-group">
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

      request.then((response) => {
        this.$router.push(`/products`);
      });
    }
  }
};
</script>

<style scoped>
.product-form {
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

.form-group {
  margin-bottom: 15px;
}

label {
  font-weight: bold;
  display: block;
  margin-bottom: 5px;
}

input, textarea {
  width: 100%;
  padding: 8px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

button {
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
  margin-top: 15px;
}

button:hover {
  background-color: #218838;
}
</style>
