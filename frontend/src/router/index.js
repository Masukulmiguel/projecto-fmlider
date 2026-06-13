import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/authStore'
import { useCompanyStore } from '@/stores/companyStore'

const Home = () => import('@/pages/Home.vue')
const About = () => import('@/pages/About.vue')
const Services = () => import('@/pages/Services.vue')
const ServiceDetail = () => import('@/pages/ServiceDetail.vue')
const Fleet = () => import('@/pages/Fleet.vue')
const News = () => import('@/pages/News.vue')
const NewsDetail = () => import('@/pages/NewsDetail.vue')
const Gallery = () => import('@/pages/Gallery.vue')
const Contact = () => import('@/pages/Contact.vue')
const Termos = () => import('@/pages/Termos.vue')
const Politicas = () => import('@/pages/Politicas.vue')

const Login = () => import('@/pages/auth/Login.vue')
const Register = () => import('@/pages/auth/Register.vue')
const ForgotPassword = () => import('@/pages/auth/ForgotPassword.vue')
const ResetPassword = () => import('@/pages/auth/ResetPassword.vue')
const ChangePassword = () => import('@/pages/auth/ChangePassword.vue')

const AdminDashboard = () => import('@/admin/pages/Dashboard.vue')
const AdminUsers = () => import('@/admin/pages/Users.vue')
const AdminServices = () => import('@/admin/pages/Services.vue')
const AdminNews = () => import('@/admin/pages/News.vue')
const AdminGallery = () => import('@/admin/pages/Gallery.vue')
const AdminPartners = () => import('@/admin/pages/Partners.vue')
const AdminContacts = () => import('@/admin/pages/Contacts.vue')
const AdminTestimonials = () => import('@/admin/pages/Testimonials.vue')
const AdminFAQs = () => import('@/admin/pages/FAQs.vue')
const AdminBanners = () => import('@/admin/pages/Banners.vue')
const AdminProfile = () => import('@/admin/pages/Profile.vue')
const AdminSettings = () => import('@/admin/pages/Settings.vue')

const ClienteDashboard = () => import('@/pages/cliente/Dashboard.vue')
const ClienteProfile = () => import('@/pages/cliente/Profile.vue')
const SetupCompany = () => import('@/pages/cliente/SetupCompany.vue')

const EmbarquesList = () => import('@/pages/cliente/embarques/List.vue')
const EmbarqueForm = () => import('@/pages/cliente/embarques/Form.vue')
const DocumentosList = () => import('@/pages/cliente/documentos/List.vue')
const ContactosList = () => import('@/pages/cliente/contactos/List.vue')
const CotacoesList = () => import('@/pages/cliente/cotacoes/List.vue')
const CotacaoForm = () => import('@/pages/cliente/cotacoes/Form.vue')

const AdminEmbarques = () => import('@/admin/pages/Embarques.vue')
const AdminDocumentos = () => import('@/admin/pages/Documentos.vue')
const AdminContactosCliente = () => import('@/admin/pages/ContactosCliente.vue')
const AdminCotacoes = () => import('@/admin/pages/Cotacoes.vue')
const AdminMessages = () => import('@/admin/pages/Messages.vue')
const AdminVisitors = () => import('@/admin/pages/Visitors.vue')
const AdminFuncionarios = () => import('@/admin/pages/Funcionarios.vue')

const FuncionarioDashboard = () => import('@/funcionario/pages/Dashboard.vue')
const FuncionarioMessages = () => import('@/funcionario/pages/Messages.vue')
const FuncionarioProfile = () => import('@/funcionario/pages/Profile.vue')
const FuncionarioEmbarques = () => import('@/funcionario/pages/Embarques.vue')
const FuncionarioCotacoes = () => import('@/funcionario/pages/Cotacoes.vue')
const FuncionarioDocumentos = () => import('@/funcionario/pages/Documentos.vue')
const FuncionarioContactos = () => import('@/funcionario/pages/Contactos.vue')
const FuncionarioClientes = () => import('@/funcionario/pages/Clientes.vue')

const ClienteMessages = () => import('@/pages/cliente/Messages.vue')

const routes = [
  { path: '/', name: 'Home', component: Home, meta: { layout: 'public' } },
  { path: '/sobre', name: 'About', component: About, meta: { layout: 'public' } },
  { path: '/servicos', name: 'Services', component: Services, meta: { layout: 'public' } },
  { path: '/servicos/:slug', name: 'ServiceDetail', component: ServiceDetail, meta: { layout: 'public' } },
  { path: '/frota', name: 'Fleet', component: Fleet, meta: { layout: 'public' } },
  { path: '/noticias', name: 'News', component: News, meta: { layout: 'public' } },
  { path: '/noticias/:slug', name: 'NewsDetail', component: NewsDetail, meta: { layout: 'public' } },
  { path: '/galeria', name: 'Gallery', component: Gallery, meta: { layout: 'public' } },
  { path: '/contacto', name: 'Contact', component: Contact, meta: { layout: 'public' } },
  { path: '/termos', name: 'Termos', component: Termos, meta: { layout: 'public' } },
  { path: '/politicas', name: 'Politicas', component: Politicas, meta: { layout: 'public' } },

  { path: '/login', name: 'Login', component: Login, meta: { layout: 'public' } },
  { path: '/registro', name: 'Register', component: Register, meta: { layout: 'public' } },
  { path: '/esqueci-senha', name: 'ForgotPassword', component: ForgotPassword, meta: { layout: 'public' } },
  { path: '/redefinir-senha/:token', name: 'ResetPassword', component: ResetPassword, meta: { layout: 'public' } },
  { path: '/mudar-senha', name: 'ChangePassword', component: ChangePassword, meta: { layout: 'public', requiresAuth: true } },

  { path: '/admin', name: 'AdminDashboard', component: AdminDashboard, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/utilizadores', name: 'AdminUsers', component: AdminUsers, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/servicos', name: 'AdminServices', component: AdminServices, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/noticias', name: 'AdminNews', component: AdminNews, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/galeria', name: 'AdminGallery', component: AdminGallery, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/parceiros', name: 'AdminPartners', component: AdminPartners, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/contactos', name: 'AdminContacts', component: AdminContacts, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/testemunhos', name: 'AdminTestimonials', component: AdminTestimonials, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/faqs', name: 'AdminFAQs', component: AdminFAQs, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/banners', name: 'AdminBanners', component: AdminBanners, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/embarques', name: 'AdminEmbarques', component: AdminEmbarques, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/documentos', name: 'AdminDocumentos', component: AdminDocumentos, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/cotacoes', name: 'AdminCotacoes', component: AdminCotacoes, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/contactos-cliente', name: 'AdminContactosCliente', component: AdminContactosCliente, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/perfil', name: 'AdminProfile', component: AdminProfile, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/mensagens', name: 'AdminMessages', component: AdminMessages, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/visitantes', name: 'AdminVisitors', component: AdminVisitors, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/funcionarios', name: 'AdminFuncionarios', component: AdminFuncionarios, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },
  { path: '/admin/configuracoes', name: 'AdminSettings', component: AdminSettings, meta: { layout: 'admin', requiresAuth: true, role: 'admin' } },

  { path: '/funcionario', name: 'FuncionarioDashboard', component: FuncionarioDashboard, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/mensagens', name: 'FuncionarioMessages', component: FuncionarioMessages, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/perfil', name: 'FuncionarioProfile', component: FuncionarioProfile, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/embarques', name: 'FuncionarioEmbarques', component: FuncionarioEmbarques, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/cotacoes', name: 'FuncionarioCotacoes', component: FuncionarioCotacoes, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/documentos', name: 'FuncionarioDocumentos', component: FuncionarioDocumentos, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/contactos', name: 'FuncionarioContactos', component: FuncionarioContactos, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },
  { path: '/funcionario/clientes', name: 'FuncionarioClientes', component: FuncionarioClientes, meta: { layout: 'funcionario', requiresAuth: true, role: 'funcionario' } },

  { path: '/dashboard', name: 'ClienteDashboard', component: ClienteDashboard, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
  { path: '/perfil', name: 'ClienteProfile', component: ClienteProfile, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
  { path: '/configurar-empresa', name: 'SetupCompany', component: SetupCompany, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente' } },
  { path: '/mensagens', name: 'ClienteMessages', component: ClienteMessages, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },

  { path: '/embarques', name: 'EmbarquesList', component: EmbarquesList, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
  { path: '/embarques/novo', name: 'EmbarqueNew', component: EmbarqueForm, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
  { path: '/embarques/:id/editar', name: 'EmbarqueEdit', component: EmbarqueForm, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },

  { path: '/documentos', name: 'DocumentosList', component: DocumentosList, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },

  { path: '/contactos', name: 'ContactosList', component: ContactosList, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },

  { path: '/cotacoes', name: 'CotacoesList', component: CotacoesList, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
  { path: '/cotacoes/novo', name: 'CotacaoNew', component: CotacaoForm, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
  { path: '/cotacoes/:id/editar', name: 'CotacaoEdit', component: CotacaoForm, meta: { layout: 'cliente', requiresAuth: true, role: 'cliente', requiresCompany: true } },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  }
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  const companyStore = useCompanyStore()

  const requiresAuth = to.meta.requiresAuth
  const requiredRole = to.meta.role
  const requiresCompany = to.meta.requiresCompany

  if (requiresAuth && !authStore.isAuthenticated) {
    return next('/login')
  }

  if (authStore.isAuthenticated) {
    if (!authStore.user) {
      const savedUser = localStorage.getItem('user')
      if (savedUser) {
        try {
          authStore.user = JSON.parse(savedUser)
        } catch (e) {
          authStore.logout()
          if (requiresAuth) return next('/login')
          return next()
        }
      } else {
        authStore.logout()
        if (requiresAuth) return next('/login')
        return next()
      }
    }

    if (authStore.user?.must_change_password && to.path !== '/mudar-senha') {
      return next('/mudar-senha')
    }

    if (to.path === '/mudar-senha' && !authStore.user?.must_change_password) {
      if (authStore.user?.role === 'admin') return next('/admin')
      if (authStore.user?.role === 'funcionario') return next('/funcionario')
      if (authStore.user?.role === 'cliente') {
        if (!authStore.companyCompleted) return next('/configurar-empresa')
        return next('/dashboard')
      }
      return next('/login')
    }

    if (requiredRole && authStore.user?.role !== requiredRole) {
      if (authStore.user?.role === 'admin') return next('/admin')
      if (authStore.user?.role === 'funcionario') return next('/funcionario')
      if (authStore.user?.role === 'cliente') {
        if (!authStore.companyCompleted) return next('/configurar-empresa')
        return next('/dashboard')
      }
      return next('/login')
    }

    if (authStore.user?.role === 'cliente' && authStore.user?.approval_status !== 'approved') {
      authStore.logout()
      return next('/login')
    }

    if (authStore.user?.role === 'cliente' && requiresCompany) {
      if (!companyStore.company) {
        await companyStore.fetch()
      }
      if (!companyStore.isCompleted) {
        return next('/configurar-empresa')
      }
    }
  }

  if (to.path === '/login' && authStore.isAuthenticated) {
    if (authStore.user?.role === 'admin') return next('/admin')
    if (authStore.user?.role === 'funcionario') return next('/funcionario')
    if (authStore.user?.role === 'cliente') {
      if (!authStore.companyCompleted) return next('/configurar-empresa')
      return next('/dashboard')
    }
  }

  next()
})

export default router
