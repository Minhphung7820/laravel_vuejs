import { createRouter, createWebHistory } from 'vue-router';
import Home from '../components/Home.vue';
import About from '../components/About.vue';

const routes = [
  { path: '/', component: Home },
  { path: '/about', component: About },
  // Thêm các route khác tại đây
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;