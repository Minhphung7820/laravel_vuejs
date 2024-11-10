<template>
  <div class="product-list">
    <h2>Product List</h2>
    <button class="create-button" @click="goToCreate">Create New Product</button>

    <div class="search-container">
      <input
        type="text"
        v-model="keyword"
        placeholder="Search products..."
        @keyup.enter="searchProducts"
      />
      <button @click="searchProducts">Search</button>
   </div>

    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
    </div>

    <ul v-else>
      <li v-for="product in formattedProducts" :key="product.id" class="product-item">
        <div class="product-info">
          <img v-if="product.avatar" :src="product.avatar" alt="Avatar Image" class="avatar" />
          <div class="product-details">
            <router-link :to="`/products/${product.id}`" class="product-link">{{ product.name }}</router-link>
            <p>{{ product.formattedPrice }} vnđ</p>
          </div>
        </div>
        <button class="delete-button" @click="deleteProduct(product.id)">Delete</button>
      </li>
    </ul>

    <!-- Nút phân trang -->
    <div class="pagination">
      <!-- Nút First -->
      <button @click="goToPage(1)" :disabled="currentPage === 1"><ChevronDoubleLeftIcon  class="size-5 text-white font-bold"/></button>

      <!-- Nút Previous -->
      <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"><ChevronLeftIcon  class="size-5 text-white font-bold"/></button>

      <!-- Các trang gần -->
      <button
        v-for="page in visiblePageNumbers"
        :key="page"
        @click="page !== '...' && goToPage(page)"
        :class="{ active: page === currentPage }"
        :disabled="page === '...'"
      >
        {{ page }}
      </button>

      <!-- Nút Next -->
      <button @click="goToPage(currentPage + 1)" :disabled="currentPage === lastPage"><ChevronRightIcon class="size-5 text-white font-bold"/></button>

      <!-- Nút Last -->
      <button @click="goToPage(lastPage)" :disabled="currentPage === lastPage"><ChevronDoubleRightIcon  class="size-5 text-white font-bold"/></button>
    </div>

  </div>
</template>

<script>
import {encodeQueryParams,decodeQueryParams} from '../../utils/functions';
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  ChevronDoubleLeftIcon,
  ChevronDoubleRightIcon
} from '@heroicons/vue/24/solid'

export default {
  components : {
    ChevronLeftIcon,
    ChevronRightIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon
  },
  inject: ['$axios'],
  data() {
    return {
      products: [],
      isLoading: false,
      currentPage: 1,
      limit: 10,
      lastPage: 1,
      keyword: '', // Biến lưu từ khóa tìm kiếm
    };
  },
  async created() {
    const encodedQuery = this.$route.query.query;
    if (encodedQuery) {
      const decodedParams = decodeQueryParams(encodedQuery);
      if (decodedParams) {
        this.currentPage = parseInt(decodedParams.page) || 1;
        this.limit = parseInt(decodedParams.limit) || 10;
        this.keyword = decodedParams.keyword || ''; // Lấy keyword từ URL nếu có
      }
    }
    await this.fetchProducts(this.currentPage, this.limit, this.keyword);
  },
  watch: {
    '$route.query.query'(newQuery) {
      const decodedParams = decodeQueryParams(newQuery);
      if (decodedParams) {
        this.currentPage = parseInt(decodedParams.page) || 1;
        this.limit = parseInt(decodedParams.limit) || 10;
        this.keyword = decodedParams.keyword || ''; // Lấy keyword từ URL nếu có
        this.fetchProducts(this.currentPage, this.limit, this.keyword);
      }
    }
  },
  computed: {
    formattedProducts() {
      // Định dạng sản phẩm với giá thành
      const products = this.products.map(product => ({
        ...product,
        formattedPrice: product.price.toLocaleString('en-US')
      }));
      console.log("Danh sách sản phẩm đã định dạng:", products); // Kiểm tra danh sách sản phẩm đã định dạng
      return products;
    },
    visiblePageNumbers() {
      const pageNumbers = [];
      const range = 2;
      const start = Math.max(1, this.currentPage - range);
      const end = Math.min(this.lastPage, this.currentPage + range);

      if (start > 1) {
        pageNumbers.push(1);
        if (start > 2) {
          pageNumbers.push('...');
        }
      }

      for (let i = start; i <= end; i++) {
        pageNumbers.push(i);
      }

      if (end < this.lastPage) {
        if (end < this.lastPage - 1) {
          pageNumbers.push('...');
        }
        pageNumbers.push(this.lastPage);
      }

      return pageNumbers;
    }
  },
  methods: {
    async fetchProducts(page = 1, limit = 10, keyword = '') {
      this.isLoading = true;
      try {
        // Truyền keyword vào API
        const response = await this.$axios.get(`/api/products?page=${page}&limit=${limit}&keyword=${keyword}`);
        console.log("Dữ liệu API trả về:", response.data); // Kiểm tra dữ liệu API
        this.products = response.data.data;
        this.currentPage = response.data.current_page;
        this.lastPage = response.data.last_page;
      } catch (error) {
        console.error("Error fetching products:", error);
      } finally {
        this.isLoading = false;
      }
    },
    async deleteProduct(id) {
      const confirmDelete = window.confirm("Are you sure you want to delete this product?");
      if (confirmDelete) {
        try {
          await this.$axios.delete(`/api/products/${id}`);
          this.fetchProducts(this.currentPage, this.limit, this.keyword);
        } catch (error) {
          console.error("Error deleting product:", error);
        }
      }
    },
    goToCreate() {
      this.$router.push('/products/create');
    },
    goToPage(page) {
      if (page >= 1 && page <= this.lastPage) {
        const encodedParams = encodeQueryParams({ page, limit: this.limit, keyword: this.keyword });
        this.$router.push({ path: this.$route.path, query: { query: encodedParams } });
      }
    },
    searchProducts() {
      // Khởi động lại tìm kiếm từ trang đầu
      this.currentPage = 1;
      const encodedParams = encodeQueryParams({ page: this.currentPage, limit: this.limit, keyword: this.keyword });
      this.$router.push({ path: this.$route.path, query: { query: encodedParams } });
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

.product-info {
  display: flex;
  align-items: center;
}

.avatar {
  width: 50px;
  height: 50px;
  margin-right: 15px;
  border-radius: 5px;
  object-fit: cover;
  border: 1px solid #ddd;
}

.product-details {
  display: flex;
  flex-direction: column;
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

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 5px;
  margin-top: 20px;
  overflow-x: auto;
  white-space: nowrap;
  padding: 10px;
  scrollbar-width: thin; /* Thanh cuộn mỏng cho Firefox */
  scrollbar-color: #3498db #f0f0f0; /* Màu thanh cuộn cho Firefox */
}

/* Thanh cuộn cho các trình duyệt Webkit */
.pagination::-webkit-scrollbar {
  height: 8px; /* Độ cao của thanh cuộn ngang */
}

.pagination::-webkit-scrollbar-track {
  background: #f0f0f0; /* Màu nền của track thanh cuộn */
  border-radius: 10px; /* Bo tròn góc track */
}

.pagination::-webkit-scrollbar-thumb {
  background-color: #3498db; /* Màu của thanh cuộn */
  border-radius: 10px; /* Bo tròn góc của thanh cuộn */
  border: 2px solid #f0f0f0; /* Tạo khoảng trống giữa thanh cuộn và track */
}

.pagination::-webkit-scrollbar-thumb:hover {
  background-color: #2980b9; /* Màu khi rê chuột vào thanh cuộn */
}

.pagination button {
  background-color: #3498db;
  color: #fff;
  padding: 5px 8px; /* Giảm kích thước padding */
  font-size: 0.9rem; /* Giảm kích thước font */
  border: none;
  border-radius: 5px;
  cursor: pointer;
}

.pagination button:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.pagination button.active {
  background-color: #2980b9;
  font-weight: bold;
}

.pagination button[disabled] {
  background-color: #ccc;
  cursor: not-allowed;
}

.pagination button.disabled {
  cursor: default;
  background-color: transparent;
  color: #555;
}

.search-container {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}

.search-container input[type="text"] {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 5px 0 0 5px;
  outline: none;
  flex: 1;
  font-size: 1rem;
}

.search-container input[type="text"]:focus {
  border-color: #3498db;
}

.search-container button {
  padding: 8px 15px;
  background-color: #3498db;
  color: #fff;
  border: none;
  border-radius: 0 5px 5px 0;
  cursor: pointer;
  font-size: 1rem;
  font-weight: bold;
}

.search-container button:hover {
  background-color: #2980b9;
}

</style>
