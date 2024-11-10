<template>
  <div class="product-detail">
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <div v-else>
      <h2>{{ product.name }}</h2>
      <p class="description" v-html="product.description"></p>
      <p class="price">Price: {{ formattedPrice }} vnđ</p>
      <p class="quantity">Quantity: {{ product.quantity }}</p>

      <div v-if="product.avatar" class="avatar-image">
        <img :src="product.avatar" alt="Avatar Image" />
      </div>

      <div v-if="product.galleries && product.galleries.length > 0" class="gallery-images">
        <h3>Gallery Images</h3>
        <div class="gallery-grid">
          <div v-for="(gallery, index) in product.galleries" :key="gallery.id" class="gallery-item">
            <img :src="gallery.url" alt="Gallery Image" />
          </div>
        </div>
      </div>

      <!-- Thêm lớp button-container để tạo khoảng cách -->
      <div class="button-container">
        <button @click="goToEdit">Edit</button>
        <button @click="goBack">Back to List</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  inject: ['$axios'],
  data() {
    return {
      product: {},
      isLoading: false
    };
  },
  async created() {
    await this.fetchProduct();
  },
  computed: {
    formattedPrice() {
      return this.product.price ? this.product.price.toLocaleString('en-US') : '';
    }
  },
  methods: {
    async fetchProduct() {
      this.isLoading = true;
      try {
        const response = await this.$axios.get(`/api/products/${this.$route.params.id}`);
        this.product = response.data;
      } catch (error) {
        console.error("Error fetching product:", error);
      } finally {
        this.isLoading = false;
      }
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

<style scoped>
.product-detail {
  padding: 20px;
  max-width: 600px;
  margin: 0 auto;
  background-color: #f5f5f5;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

h2 {
  color: #333;
  margin-bottom: 10px;
}

.description {
  font-size: 1rem;
  color: #666;
  margin-bottom: 10px;
}

.price, .quantity {
  font-size: 1.1rem;
  margin-bottom: 10px;
}

.avatar-image img {
  width: 100%;
  max-width: 300px;
  margin-bottom: 20px;
  border-radius: 5px;
}

.gallery-images {
  margin-top: 20px;
  margin-bottom: 20px; /* Tạo khoảng cách phía dưới gallery */
}

.gallery-grid {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.gallery-item {
  width: 100px;
  height: 100px;
  overflow: hidden;
  border: 1px solid #ddd;
  border-radius: 5px;
}

.gallery-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.button-container {
  margin-top: 20px; /* Tạo khoảng cách giữa gallery và các nút */
}

button {
  background-color: #3498db;
  color: #fff;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-right: 10px;
}

button:hover {
  background-color: #2980b9;
}
</style>
