import { createRouter, createWebHistory } from 'vue-router'

import Login from '@/views/Login.vue'

// Lecturer Pages
import LecturerDashboard from '@/views/lecturer/LecturerDashboard.vue'
import LecturerProfile from '@/views/lecturer/LecturerProfile.vue'
import LecturerManageStudents from '@/views/lecturer/LecturerManageStudents.vue'
import LecturerCourses from '@/views/lecturer/LecturerCourses.vue'
import LecturerAssessments from '@/views/lecturer/LecturerAssessments.vue'
import LecturerMarks from '@/views/lecturer/LecturerMarks.vue'
import LecturerRemarkRequests from '@/views/lecturer/LecturerRemarkRequests.vue'

// Student Pages
import StudentDashboard from '@/views/student/StudentDashboard.vue'
import PerformanceToolsLayout from '@/views/student/performance/PerformanceToolsLayout.vue'
import GpaCalculator from '@/views/student/performance/GpaCalculator.vue'
import CumulativeGpa from '@/views/student/performance/CumulativeGpa.vue'
import WhatIf from '@/views/student/performance/WhatIf.vue'

// Advisor Pages
import AdvisorDashboard from '@/views/advisor/AdvisorDashboard.vue'
import AdvisorAnalytics from '@/views/advisor/AdvisorAnalytics.vue'
import AdvisorMarkReview from '@/views/advisor/AdvisorMarkReview.vue'
import AdvisorStudentList from '@/views/advisor/AdvisorStudentList.vue'

// Admin Layout
import AdminLayout from '@/layouts/AdminLayout.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },

  // Lecturer Routes
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/lecturer/profile', component: LecturerProfile },
  { path: '/lecturer/students', component: LecturerManageStudents },
  { path: '/lecturer/courses', component: LecturerCourses },
  { path: '/lecturer/courses/:id/assessments', component: LecturerAssessments },
  { path: '/lecturer/courses/:id/marks', component: LecturerMarks },
  { path: '/lecturer/remark-requests', component: LecturerRemarkRequests },

  // Student Routes
  { path: '/student/dashboard', component: StudentDashboard },
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
  {
    path: '/student/course/:id',
    component: () => import('@/views/student/CourseMarksLayout.vue'),
    children: [
      {
        path: '',
        name: 'StudentCourseMarks',
        component: () => import('@/views/student/StudentCourseMarks.vue')
      },
      {
        path: 'compare',
        name: 'CompareMarks',
        component: () => import('@/views/student/CompareMarks.vue')
      }
    ]
  },
  { path: '/student/request-remark', name: 'RequestRemark', component: () => import('@/views/student/RequestRemark.vue') },

  // Advisor Routes
  { path: '/advisor/dashboard', component: AdvisorDashboard },
  { path: '/advisor/students', name: 'AdvisorStudentList', component: AdvisorStudentList },
  { path: '/advisor/reviews', name: 'AdvisorMarkReview', component: AdvisorMarkReview },
  { path: '/advisor/analytics', name: 'AdvisorAnalytics', component: AdvisorAnalytics },

  // ✅ Admin Routes (all nested in AdminLayout)
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'AdminDashboard',
        component: () => import('@/views/admin/AdminDashboard.vue')
      },
      {
        path: 'users',
        name: 'ManageUsers',
        component: () => import('@/views/admin/ManageUsers.vue')
      },
{
  path: 'assign-lecturers',
  name: 'AssignLecturer',
  component: () => import('@/views/admin/AssignLecturers.vue')
},
      {
        path: 'logs',
        name: 'SystemLogs',
        component: () => import('@/views/admin/Logs.vue')
      },
      {
        path: 'reset',
        name: 'ResetPassword',
        component: () => import('@/views/admin/ResetPassword.vue')
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
