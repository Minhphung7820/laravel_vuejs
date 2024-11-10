<template>
  <div class="product-form">
    <h2>{{ isEditMode ? 'Edit' : 'Create' }} Product</h2>

    <!-- Hiển thị vòng xoay loading trong khi chờ API -->
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <!-- Hiển thị form khi không loading -->
    <form v-else @submit.prevent="submitForm">
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
export default {
  inject: ['$axios'],
  data() {
    return {
      product: {
        name: '',
        description: '',
        price: '',
        quantity: ''
      },
      isEditMode: false,
      isLoading: false // Thêm biến isLoading để kiểm tra trạng thái loading
    };
  },
  async created() {
    if (this.$route.params.id) {
      this.isEditMode = true;
      await this.fetchProduct();
    }
  },
  methods: {
    async fetchProduct() {
      this.isLoading = true; // Bật trạng thái loading
      try {
        const response = await this.$axios.get(`/api/products/${this.$route.params.id}`);
        this.product = response.data;
      } catch (error) {
        console.error("Error fetching product:", error);
      } finally {
        this.isLoading = false; // Tắt trạng thái loading sau khi API hoàn tất
      }
    },
    async submitForm() {
      this.isLoading = true; // Bật trạng thái loading khi gửi biểu mẫu
      try {
        const request = this.isEditMode
          ? this.$axios.put(`/api/products/${this.$route.params.id}`, this.product)
          : this.$axios.post('/api/products', this.product);

        await request;
        this.$router.push('/products');
      } catch (error) {
        console.error("Error submitting form:", error);
      } finally {
        this.isLoading = false; // Tắt trạng thái loading sau khi gửi xong
      }
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
