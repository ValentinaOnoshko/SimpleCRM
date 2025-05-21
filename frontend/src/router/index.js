import { createRouter, createWebHistory } from 'vue-router';
import apiClient from '../api/apiClient.js';
import ProfileClient from '../views/ClientProfile.vue';
import ProfilePerformer from '../views/PerformerProfile.vue';
import Deals from '../views/DealList.vue';
import DealView from '../views/DealView.vue';
import DealEdit from '../views/DealEdit.vue';
import AccountSettings from '../views/AccountSettings.vue';
import NotFound from '../views/NotFound.vue';
import HomePage from '../views/HomePage.vue';
import LoginPage from '../views/LoginPage.vue';
import DealCreate from '../views/DealCreate.vue';

function isLocalStorageAvailable() {
  try {
    const testKey = '__test__';
    localStorage.setItem(testKey, testKey);
    localStorage.removeItem(testKey);
    return true;
  } catch {
    return false;
  }
}

const requireAuth = (expectedRole = null) => {
  return async (to, from, next) => {
    try {
      if (!isLocalStorageAvailable()) {
        console.warn('localStorage недоступен');
        return next('/login');
      }

      const token = localStorage.getItem('token');
      if (!token) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
        return next('/');
      }

      const response = await apiClient.get('/profile');
      const user = response.data;

      if (!user.id && to.params.userId) {
        user.id = parseInt(to.params.userId);
      }

      if (to.params.userId) {
        const requestedUserId = parseInt(to.params.userId);
        const currentUserId = parseInt(user.id);
        if (isNaN(requestedUserId) || isNaN(currentUserId) || requestedUserId !== currentUserId) {
          return next('/');
        }
      }

      if (expectedRole) {
        const storedRole = localStorage.getItem('role');
        if (storedRole !== expectedRole) {
          return next('/not-found');
        }
      }

      to.meta.profile = user;
      next();
    } catch (error) {
      console.error('Auth Error:', error);
      if (isLocalStorageAvailable()) {
        localStorage.removeItem('token');
        localStorage.removeItem('role');
      }
      if (error.response?.status === 401) {
        next('/');
      } else {
        next('/not-found');
      }
    }
  };
};

const routes = [
  {
    path: '/',
    component: HomePage,
    meta: { requiresAuth: false }
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
    beforeEnter: (to, from, next) => {
      if (!isLocalStorageAvailable()) {
        next();
        return;
      }

      const token = localStorage.getItem('token');
      if (token) {
        const role = localStorage.getItem('role');
        const userId = localStorage.getItem('userId');
        next(`/${role}/${userId}/profile`);
      } else {
        next();
      }
    },
    meta: { requiresAuth: false }
  },
  {
    path: '/deals',
    component: Deals,
    beforeEnter: requireAuth(),
    meta: { requiresAuth: true }
  },
  {
    path: '/deals/:id',
    component: DealView,
    props: true,
    beforeEnter: requireAuth(),
    meta: { requiresAuth: true }
  },
  {
    path: '/deals/:id/edit',
    component: DealEdit,
    props: true,
    beforeEnter: requireAuth(),
    meta: { requiresAuth: true }
  },
  {
    path: '/deals/edit/new',
    component: DealEdit,
    props: { id: 'new' },
    beforeEnter: requireAuth(),
    meta: { requiresAuth: true }
  },
  {
    path: '/deals/create',
    component: DealCreate,
    meta: { requiresAuth: true }
  },
  {
    path: '/client/:userId/profile',
    component: ProfileClient,
    props: true,
    beforeEnter: requireAuth('client'),
    meta: { requiresAuth: true }
  },
  {
    path: '/performer/:userId/profile',
    component: ProfilePerformer,
    props: true,
    beforeEnter: requireAuth('performer'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    component: AccountSettings,
    beforeEnter: requireAuth(),
    meta: { requiresAuth: true }
  },
  {
    path: '/not-found',
    component: NotFound,
    meta: { requiresAuth: false }
  },
  {
    path: '/auth/vk/callback',
    component: () => import('../components/RegisterForm.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/not-found',
    meta: { requiresAuth: false }
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
