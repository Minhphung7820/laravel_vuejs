import { createRouter, createWebHistory } from 'vue-router';
import Home from '../components/Home.vue';
import About from '../components/About.vue';
import ProductList from '../components/Product/ProductList.vue';
import ProductForm from '../components/Product/ProductForm.vue';
import ProductDetail from '../components/Product/ProductDetail.vue';
import NotFound from '../components/NotFound.vue';
import Login from '../components/Auth/Login.vue';
import Register from '../components/Auth/Register.vue';
// prefix
const prefixRouteProduct = 'products';
//
const routes = [
  { path: '/', component: Home },
  { path: '/login', component: Login },
  { path: '/register', component: Register },
  { path: '/about', component: About },
  { path: `/${prefixRouteProduct}`, component: ProductList, meta: { requiresAuth: true } },
  { path: `/${prefixRouteProduct}/create`, component: ProductForm, meta: { requiresAuth: true } },
  { path: `/${prefixRouteProduct}/:id`, component: ProductDetail, meta: { requiresAuth: true } },
  { path: `/${prefixRouteProduct}/:id/edit`, component: ProductForm, meta: { requiresAuth: true } },
  // Route 404 phải đặt ở cuối
  { path: '/:pathMatch(.*)*', component: NotFound } // Bắt mọi đường dẫn không phù hợp
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else {
    next();
  }
});

export default router;