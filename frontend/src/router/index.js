import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue';
import StudentDashboard from '@/views/student/StudentDashboard.vue';
// import LecturerDashboard from '@/views/lecturer/LecturerDashboard.vue';
// import AdvisorDashboard from '@/views/advisor/AdvisorDashboard.vue';
// import AdminDashboard from '@/views/admin/AdminDashboard.vue';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/dashboard/student', component: StudentDashboard },
  // { path: '/dashboard/lecturer', component: LecturerDashboard },
  // { path: '/dashboard/advisor', component: AdvisorDashboard },
  // { path: '/dashboard/admin', component: AdminDashboard }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
