<template>
    <div>
        <!-- Thanh điều hướng -->
        <nav>
            <router-link to="/">Home</router-link>
            <router-link to="/about">About</router-link>
            <router-link to="/products">Product</router-link>
            <router-link v-if="isAuthenticated" to="/get-profile">Get Profile</router-link>
            <router-link v-else to="/login">Login</router-link>
        </nav>

        <!-- Hiển thị loading trong khi component đang tải -->
        <div v-if="isLoading" class="loading-overlay">
            <div class="spinner"></div>
        </div>

        <!-- Đây là nơi Vue Router sẽ hiển thị các component theo route -->
        <router-view v-else></router-view>
    </div>
</template>

<script>
export default {
    name: 'App',
    data() {
        return {
            isAuthenticated: false,
            isLoading: false
        };
    },
    created() {
        // Kiểm tra token khi component được khởi tạo
        this.checkAuthentication();
    },
    watch: {
        // Bắt đầu chế độ loading khi route thay đổi
        '$route'() {
            this.loadComponent();
        }
    },
    methods: {
        checkAuthentication() {
            // Kiểm tra xem token có tồn tại trong localStorage không
            this.isAuthenticated = !!localStorage.getItem('token');
        },
        async loadComponent() {
            this.isLoading = true; // Bắt đầu loading
            await this.$nextTick(); // Đợi cho đến khi cập nhật giao diện

            // Giả lập thời gian chờ cho việc tải dữ liệu
            setTimeout(() => {
                this.isLoading = false; // Tắt loading khi component đã được tải
            }, 500); // Điều chỉnh thời gian theo nhu cầu
        }
    }
};
</script>

<style scoped>
/* Phong cách CSS cho App.vue */
nav {
    padding: 10px;
    background-color: #333;
    color: white;
}

nav a {
    color: white;
    margin-right: 10px;
    text-decoration: none;
}

nav a.router-link-active {
    font-weight: bold;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

/* Vòng xoay spinner */
.spinner {
    border: 8px solid rgba(255, 255, 255, 0.3); /* Màu viền mờ */
    border-top: 8px solid #3498db; /* Màu viền vòng xoay */
    border-radius: 50%;
    width: 60px;
    height: 60px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
