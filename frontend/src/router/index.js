import { createRouter, createWebHistory } from 'vue-router';
import Login from '@/views/Login.vue';
import StudentDashboard from '@/views/student/StudentDashboard.vue';
import LecturerDashboard from '@/views/lecturer/LecturerDashboard.vue';
import AdvisorDashboard from '@/views/advisor/AdvisorDashboard.vue';
import AdminDashboard from '@/views/admin/AdminDashboard.vue';
import LecturerProfile from '@/views/lecturer/LecturerProfile.vue';
import AdvisorStudentList from '@/views/advisor/AdvisorStudentList.vue';
import AdvisorMarkReview from '@/views/advisor/AdvisorMarkReview.vue'; 
import AdvisorAnalytics from '@/views/advisor/AdvisorAnalytics.vue';

import PerformanceToolsLayout from '@/views/student/performance/PerformanceToolsLayout.vue';
import GpaCalculator from '@/views/student/performance/GpaCalculator.vue';
import CumulativeGpa from '@/views/student/performance/CumulativeGpa.vue';
import WhatIf from '@/views/student/performance/WhatIf.vue';

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },
  { path: '/student/dashboard', component: StudentDashboard },
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/advisor/dashboard', component: AdvisorDashboard },
  { path: '/admin/dashboard', component: AdminDashboard },
  { path: '/lecturer/profile', component: LecturerProfile },
  { path: '/advisor/students', name: 'AdvisorStudentList', component: AdvisorStudentList },
  { path: '/advisor/reviews', name: 'AdvisorMarkReview', component: AdvisorMarkReview },
  { path: '/advisor/analytics', name: 'AdvisorAnalytics', component: AdvisorAnalytics },

  {
    path: '/student/performance',
    component: PerformanceToolsLayout,
    children: [
      { path: '', redirect: '/student/performance/gpa' },
      { path: 'gpa', component: GpaCalculator },
      { path: 'cgpa', component: CumulativeGpa },
      { path: 'what-if', component: WhatIf }
    ]
  }

  
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;

