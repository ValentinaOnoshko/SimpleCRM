import { createRouter, createWebHistory } from 'vue-router'

import ProfileClient from '../views/ClientProfile.vue'
import ProfilePerformer from '../views/PerformerProfile.vue'
import Deals from '../views/DealsPage.vue'
import DealView from '../views/DealView.vue'
import DealEdit from '../views/DealEdit.vue'
import Settings from '../views/AccountSettings.vue'
import NotFound from '../views/NotFound.vue'

const routes = [
  { path: '/', redirect: '/deals' },
  { path: '/deals', component: Deals },
  { path: '/deals/:id', component: DealView },
  { path: '/deals/edit/:id', component: DealEdit },
  { path: '/client/profile', component: ProfileClient },
  { path: '/performer/profile', component: ProfilePerformer },
  { path: '/settings', component: Settings },
  { path: '/:pathMatch(.*)*', component: NotFound },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

export default router
