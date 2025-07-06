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
import AdvisorProfile from '@/views/advisor/AdvisorProfile.vue';
import HighRiskStudents from '@/views/advisor/HighRiskStudents.vue';

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
  { path: '/advisor/dashboard', name: 'AdvisorDashboard', component: AdvisorDashboard },
  { path: '/advisor/profile', name: 'AdvisorProfile', component: AdvisorProfile },
  { path: '/advisor/high-risk-students', name: 'HighRiskStudents', component: HighRiskStudents},
  { path: '/advisor/notes', name: 'AdvisorNotes', component: () => import('@/views/advisor/AdvisorNotes.vue')},


  {
    path: '/student/performance',
    component: PerformanceToolsLayout,
    children: [
      { path: '', redirect: '/student/performance/gpa' },
      { path: 'gpa', component: GpaCalculator },
      { path: 'cgpa', component: CumulativeGpa },
      { path: 'what-if', component: WhatIf }
    ]
<<<<<<< HEAD
=======
  },

  {
  path: '/student/course/:id',
  component: () => import('@/views/student/CourseMarksLayout.vue'),
  children: [
    {
      path: '',
      name: 'StudentCourseMarks',
      component: () => import('@/views/student/StudentCourseMarks.vue') // mark breakdown
    },
    {
      path: 'compare',
      name: 'CompareMarks',
      component: () => import('@/views/student/CompareMarks.vue') // chart view
    }
  ]
},


  // Advisor routes
  { path: '/advisor/students', name: 'AdvisorStudentList', component: AdvisorStudentList },
  { path: '/advisor/reviews', name: 'AdvisorMarkReview', component: AdvisorMarkReview },
  { path: '/advisor/analytics', name: 'AdvisorAnalytics', component: AdvisorAnalytics },

  // Flat admin routes using AppLayout inside each page
  {
    path: '/admin/dashboard',
    name: 'AdminDashboard',
    component: AdminDashboard
  },
  {
    path: '/admin/users',
    name: 'ManageUsers',
    component: ManageUsers
  },
  {
    path: '/admin/assign-lecturers',
    name: 'AssignLecturers',
    component: AssignLecturers
  },
  {
    path: '/admin/logs',
    name: 'SystemLogs',
    component: Logs
  },
  {
    path: '/admin/reset',
    name: 'ResetPassword',
    component: ResetPassword
>>>>>>> 3c062c7bc990c543fbbad0eef06913c33abe026d
  }

  
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;

