import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'

// Public pages
import Home from '@/pages/Home.vue'
import About from '@/pages/About.vue'
import Services from '@/pages/Services.vue'
import ServiceDetail from '@/pages/ServiceDetail.vue'
import Fleet from '@/pages/Fleet.vue'
import News from '@/pages/News.vue'
import NewsDetail from '@/pages/NewsDetail.vue'
import Gallery from '@/pages/Gallery.vue'
import Contact from '@/pages/Contact.vue'

// Auth pages
import Login from '@/pages/auth/Login.vue'
import Register from '@/pages/auth/Register.vue'
import ForgotPassword from '@/pages/auth/ForgotPassword.vue'
import ResetPassword from '@/pages/auth/ResetPassword.vue'

// Admin pages
import AdminDashboard from '@/admin/pages/Dashboard.vue'
import AdminUsers from '@/admin/pages/Users.vue'
import AdminServices from '@/admin/pages/Services.vue'
import AdminNews from '@/admin/pages/News.vue'
import AdminGallery from '@/admin/pages/Gallery.vue'
import AdminPartners from '@/admin/pages/Partners.vue'
import AdminContacts from '@/admin/pages/Contacts.vue'
import AdminTestimonials from '@/admin/pages/Testimonials.vue'
import AdminFAQs from '@/admin/pages/FAQs.vue'
import AdminBanners from '@/admin/pages/Banners.vue'
import AdminProfile from '@/admin/pages/Profile.vue'

const routes = [
  // Public routes
  {
    path: '/',
    name: 'Home',
    component: Home,
    meta: { requiresAuth: false }
  },
  {
    path: '/sobre',
    name: 'About',
    component: About,
    meta: { requiresAuth: false }
  },
  {
    path: '/servicos',
    name: 'Services',
    component: Services,
    meta: { requiresAuth: false }
  },
  {
    path: '/servicos/:slug',
    name: 'ServiceDetail',
    component: ServiceDetail,
    meta: { requiresAuth: false }
  },
  {
    path: '/frota',
    name: 'Fleet',
    component: Fleet,
    meta: { requiresAuth: false }
  },
  {
    path: '/noticias',
    name: 'News',
    component: News,
    meta: { requiresAuth: false }
  },
  {
    path: '/noticias/:slug',
    name: 'NewsDetail',
    component: NewsDetail,
    meta: { requiresAuth: false }
  },
  {
    path: '/galeria',
    name: 'Gallery',
    component: Gallery,
    meta: { requiresAuth: false }
  },
  {
    path: '/contacto',
    name: 'Contact',
    component: Contact,
    meta: { requiresAuth: false }
  },

  // Auth routes
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresAuth: false }
  },
  {
    path: '/registro',
    name: 'Register',
    component: Register,
    meta: { requiresAuth: false }
  },
  {
    path: '/esqueci-senha',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { requiresAuth: false }
  },
  {
    path: '/redefinir-senha/:token',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { requiresAuth: false }
  },

  // Admin routes
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/utilizadores',
    name: 'AdminUsers',
    component: AdminUsers,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/servicos',
    name: 'AdminServices',
    component: AdminServices,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/noticias',
    name: 'AdminNews',
    component: AdminNews,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/galeria',
    name: 'AdminGallery',
    component: AdminGallery,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/parceiros',
    name: 'AdminPartners',
    component: AdminPartners,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/contactos',
    name: 'AdminContacts',
    component: AdminContacts,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/testemunhos',
    name: 'AdminTestimonials',
    component: AdminTestimonials,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/faqs',
    name: 'AdminFAQs',
    component: AdminFAQs,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/banners',
    name: 'AdminBanners',
    component: AdminBanners,
    meta: { requiresAuth: true, admin: true }
  },
  {
    path: '/admin/perfil',
    name: 'AdminProfile',
    component: AdminProfile,
    meta: { requiresAuth: true, admin: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Navigation guard for authentication
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth) {
    if (authStore.isAuthenticated) {
      next()
    } else {
      next('/login')
    }
  } else {
    next()
  }
})

export default router
