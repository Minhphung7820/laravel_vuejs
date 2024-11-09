<template>
  <div class="product-detail">
    <h2>{{ product.name }}</h2>
    <p class="description">{{ product.description }}</p>
    <p class="price">Price: {{ formattedPrice }} vnđ</p>
    <p class="quantity">Quantity: {{ product.quantity }}</p>
    <button @click="goToEdit">Edit</button>
    <button @click="goBack">Back to List</button>
  </div>
</template>

<script>

export default {
  inject: ['$axios'],
  data() {
    return {
      product: {}
    };
  },
  created() {
    this.fetchProduct();
  },
  computed: {
    // Định dạng giá sản phẩm thành chuỗi có dấu phẩy
    formattedPrice() {
      return this.product.price ? this.product.price.toLocaleString('en-US') : '';
    }
  },
  methods: {
    fetchProduct() {
      this.$axios.get(`/api/products/${this.$route.params.id}`)
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
