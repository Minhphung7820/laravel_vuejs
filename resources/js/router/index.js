import { createRouter, createWebHistory } from 'vue-router';
import Home from '../components/Home.vue';
import About from '../components/About.vue';
import ProductList from '../components/Product/ProductList.vue';
import ProductForm from '../components/Product/ProductForm.vue';
import ProductDetail from '../components/Product/ProductDetail.vue';

const routes = [
  { path: '/', component: Home },
  { path: '/about', component: About },
  { path: '/products', component: ProductList },
  { path: '/products/create', component: ProductForm },
  { path: '/products/:id', component: ProductDetail },
  { path: '/products/:id/edit', component: ProductForm }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;