import { createRouter, createWebHistory } from 'vue-router';

// General routes
import Login from '@/views/Login.vue';
import StudentDashboard from '@/views/student/StudentDashboard.vue';
import LecturerDashboard from '@/views/lecturer/LecturerDashboard.vue';
import AdvisorDashboard from '@/views/advisor/AdvisorDashboard.vue';
import LecturerProfile from '@/views/lecturer/LecturerProfile.vue';
import LecturerManageStudents from '@/views/lecturer/LecturerManageStudents.vue';

// Student performance routes
import PerformanceToolsLayout from '@/views/student/performance/PerformanceToolsLayout.vue';
import GpaCalculator from '@/views/student/performance/GpaCalculator.vue';
import CumulativeGpa from '@/views/student/performance/CumulativeGpa.vue';
import WhatIf from '@/views/student/performance/WhatIf.vue';

// Admin layout + dashboard
import AdminLayout from '@/views/admin/AdminLayout.vue';
import AdminDashboard from '@/views/admin/AdminDashboard.vue';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/student/dashboard', component: StudentDashboard },
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/advisor/dashboard', component: AdvisorDashboard },
  { path: '/lecturer/profile', component: LecturerProfile },
  { path: '/lecturer/students', component: LecturerManageStudents },

  {
    path: '/student/performance',
    component: PerformanceToolsLayout,
    children: [
      { path: '', redirect: '/student/performance/gpa' },
      { path: 'gpa', component: GpaCalculator },
      { path: 'cgpa', component: CumulativeGpa },
      { path: 'what-if', component: WhatIf }
    ]
  },

  // ✅ Admin section with layout and nested routes
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: '', redirect: '/admin/dashboard' },
      { path: 'dashboard', component: AdminDashboard },
      { path: 'users', component: () => import('@/views/admin/ManageUsers.vue') },
      { path: 'assign', component: () => import('@/views/admin/AssignLecturers.vue') },
      { path: 'logs', component: () => import('@/views/admin/Logs.vue') },
      { path: 'reset', component: () => import('@/views/admin/ResetPassword.vue') }
    ]
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
