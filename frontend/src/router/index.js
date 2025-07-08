import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/Login.vue'

// Admin
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import ManageUsers from '@/views/admin/ManageUsers.vue'
import AssignLecturers from '@/views/admin/AssignLecturers.vue'
import Logs from '@/views/admin/Logs.vue'
import ResetPassword from '@/views/admin/ResetPassword.vue'

// Student
import StudentDashboard from '@/views/student/StudentDashboard.vue'
import StudentProfile from '@/views/student/StudentProfile.vue'
import PerformanceToolsLayout from '@/views/student/performance/PerformanceToolsLayout.vue'
import GpaCalculator from '@/views/student/performance/GpaCalculator.vue'
import CumulativeGpa from '@/views/student/performance/CumulativeGpa.vue'
import WhatIf from '@/views/student/performance/WhatIf.vue'

// Lecturer
import LecturerDashboard from '@/views/lecturer/LecturerDashboard.vue'
import LecturerProfile from '@/views/lecturer/LecturerProfile.vue'
import LecturerManageStudents from '@/views/lecturer/LecturerManageStudents.vue'
import LecturerCourses from '@/views/lecturer/LecturerCourses.vue'
import LecturerAssessments from '@/views/lecturer/LecturerAssessments.vue'
import LecturerMarks from '@/views/lecturer/LecturerMarks.vue'
import LecturerRemarkRequests from '@/views/lecturer/LecturerRemarkRequests.vue'

// Advisor
import AdvisorDashboard from '@/views/advisor/AdvisorDashboard.vue'
import AdvisorAnalytics from '@/views/advisor/AdvisorAnalytics.vue'
import AdvisorMarkReview from '@/views/advisor/AdvisorMarkReview.vue'
import AdvisorStudentList from '@/views/advisor/AdvisorStudentList.vue'
import AdvisorProfile from '@/views/advisor/AdvisorProfile.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login },

  // Admin Routes (layout)
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboard },
      { path: 'users', name: 'ManageUsers', component: ManageUsers },
      { path: 'assign-lecturers', name: 'AssignLecturers', component: AssignLecturers },
      { path: 'logs', name: 'SystemLogs', component: Logs },
      { path: 'reset', name: 'ResetPassword', component: ResetPassword },
    ]
  },

  // Lecturer Routes
  { path: '/lecturer/dashboard', component: LecturerDashboard },
  { path: '/lecturer/profile', component: LecturerProfile },
  { path: '/lecturer/students', component: LecturerManageStudents },
  { path: '/lecturer/courses', component: LecturerCourses },
  { path: '/lecturer/courses/:id/assessments', component: LecturerAssessments },
  { path: '/lecturer/courses/:id/marks', component: LecturerMarks },
  { path: '/lecturer/remark-requests', component: LecturerRemarkRequests },

  // Advisor Routes
  { path: '/advisor/dashboard', name: 'AdvisorDashboard', component: AdvisorDashboard },
  { path: '/advisor/students', name: 'AdvisorStudentList', component: AdvisorStudentList },
  { path: '/advisor/reviews', name: 'AdvisorMarkReview', component: AdvisorMarkReview },
  { path: '/advisor/analytics', name: 'AdvisorAnalytics', component: AdvisorAnalytics },
  { path: '/advisor/profile', name: 'AdvisorProfile', component: AdvisorProfile },
  { path: '/advisor/notes', name: 'AdvisorNotes', component: () => import('@/views/advisor/AdvisorNotes.vue') },

  // Student Routes
  { path: '/student/dashboard', component: StudentDashboard },
  { path: '/student/profile', component: StudentProfile },
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
      { path: '', name: 'StudentCourseMarks', component: () => import('@/views/student/StudentCourseMarks.vue') },
      { path: 'compare', name: 'CompareMarks', component: () => import('@/views/student/CompareMarks.vue') },
      { path: 'rank', name: 'RankAndPercentile', component: () => import('@/views/student/RankAndPercentile.vue') }
    ]
  },
  { path: '/student/request-remark', name: 'RequestRemark', component: () => import('@/views/student/RequestRemark.vue') },
  { path: '/student/appeal-remark', name: 'AppealRemark', component: () => import('@/views/student/StudentAppealRemark.vue') }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

export default router;
