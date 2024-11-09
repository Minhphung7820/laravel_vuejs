<template>
  <div class="product-list">
    <h2>Product List</h2>
    <button class="create-button" @click="goToCreate">Create New Product</button>
    <ul>
      <li v-for="product in formattedProducts" :key="product.id" class="product-item">
        <router-link :to="`/products/${product.id}`" class="product-link">{{ product.name }}</router-link>
        {{ product.formattedPrice }} vnđ
        <button class="delete-button" @click="deleteProduct(product.id)">Delete</button>
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  inject: ['$axios'],
  data() {
    return {
      products: []
    };
  },
  async created() {
    await this.fetchProducts();
  },
  computed: {
    formattedProducts() {
      return this.products.map(product => ({
        ...product,
        formattedPrice: product.price.toLocaleString('en-US')
      }));
    }
  },
  methods: {
    async fetchProducts() {
      try {
        const response = await this.$axios.get('/api/products');
        this.products = response.data;
        this.isAuthenticated = true; // Xác thực thành công, cho phép render component
      } catch (error) {
        console.error("Error deleting product:", error);
      }
    },
    async deleteProduct(id) {
      const confirmDelete = window.confirm("Are you sure you want to delete this product?");
      if (confirmDelete) {
        try {
          await this.$axios.delete(`/api/products/${id}`);
          this.fetchProducts(); // Cập nhật lại danh sách sản phẩm sau khi xóa thành công
        } catch (error) {
          console.error("Error deleting product:", error);
        }
      }
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
