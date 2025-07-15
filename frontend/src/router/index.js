import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/Login.vue'

// Admin
import AdminLayout from '@/layouts/AdminLayout.vue'
import AdminDashboard from '@/views/admin/AdminDashboard.vue'
import ManageUsers from '@/views/admin/ManageUsers.vue'
import AssignLecturers from '@/views/admin/AssignLecturers.vue'
import Logs from '@/views/admin/Logs.vue'
import ResetPassword from '@/views/admin/ResetPassword.vue'
import AdminProfile from '@/views/admin/AdminProfile.vue'

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
import AdviseeProgress from '@/views/advisor/AdviseeProgress.vue'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', component: Login, meta: { title: 'Login' } },

  // Admin Routes (layout)
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      { path: 'dashboard', name: 'AdminDashboard', component: AdminDashboard, meta: { title: 'Admin Dashboard' } },
      { path: 'users', name: 'ManageUsers', component: ManageUsers, meta: { title: 'Manage Users' } },
      { path: 'assign-lecturers', name: 'AssignLecturers', component: AssignLecturers, meta: { title: 'Assign Lecturers' } },
      { path: 'logs', name: 'SystemLogs', component: Logs, meta: { title: 'System Logs' } },
      { path: 'reset', name: 'ResetPassword', component: ResetPassword, meta: { title: 'Reset Password' } },
      { path: 'profile', name: 'AdminProfile', component: AdminProfile, meta: { title: 'My Profile' } }
    ]
  },

  // Lecturer Routes
  { path: '/lecturer/dashboard', component: LecturerDashboard, meta: { title: 'Lecturer Dashboard' } },
  { path: '/lecturer/profile', component: LecturerProfile, meta: { title: 'My Profile' } },
  { path: '/lecturer/students', component: LecturerManageStudents, meta: { title: 'Manage Students' } },
  { path: '/lecturer/courses', component: LecturerCourses, meta: { title: 'My Courses' } },
  { path: '/lecturer/courses/:id/assessments', component: LecturerAssessments, meta: { title: 'Assessments' } },
  { path: '/lecturer/courses/:id/marks', component: LecturerMarks, meta: { title: 'Assessment Marks' } },
  { path: '/lecturer/remark-requests', component: LecturerRemarkRequests, meta: { title: 'Remark Requests' } },

  // Advisor Routes
  { path: '/advisor/dashboard', name: 'AdvisorDashboard', component: AdvisorDashboard, meta: { title: 'Advisor Dashboard' } },
  { path: '/advisor/students', name: 'AdvisorStudentList', component: AdvisorStudentList, meta: { title: 'Student List' } },
  { path: '/advisor/reviews', name: 'AdvisorMarkReview', component: AdvisorMarkReview, meta: { title: 'Mark Review' } },
  { path: '/advisor/advisee/:id/progress', name: 'AdviseeProgress', component: AdviseeProgress, meta: { title: 'Advisee Progress' } },
  { path: '/advisor/analytics', name: 'AdvisorAnalytics', component: AdvisorAnalytics, meta: { title: 'Analytics' } },
  { path: '/advisor/profile', name: 'AdvisorProfile', component: AdvisorProfile, meta: { title: 'My Profile' } },
  { path: '/advisor/notes', name: 'AdvisorNotes', component: () => import('@/views/advisor/AdvisorNotes.vue'), meta: { title: 'Notes' } },

  // Student Routes
  { path: '/student/dashboard', component: StudentDashboard, meta: { title: 'Student Dashboard' } },
  { path: '/student/profile', component: StudentProfile, meta: { title: 'My Profile' } },
  {
    path: '/student/performance',
    component: PerformanceToolsLayout,
    children: [
      { path: '', redirect: '/student/performance/gpa' },
      { path: 'gpa', component: GpaCalculator },
      { path: 'cgpa', component: CumulativeGpa },
      { path: 'what-if', component: WhatIf }
    ], 
    meta: { title: 'Performance' }
  },
  {
    path: '/student/course/:id',
    component: () => import('@/views/student/CourseMarksLayout.vue'),
    children: [
      { path: '', name: 'StudentCourseMarks', component: () => import('@/views/student/StudentCourseMarks.vue') },
      { path: 'compare', name: 'CompareMarks', component: () => import('@/views/student/CompareMarks.vue') },
      { path: 'rank', name: 'RankAndPercentile', component: () => import('@/views/student/RankAndPercentile.vue') }
    ],
    meta: { title: 'Course Marks' }
  },
  { path: '/student/request-remark', name: 'RequestRemark', component: () => import('@/views/student/RequestRemark.vue'), meta: { title: 'Request Remark' } },
  { path: '/student/appeal-remark', name: 'AppealRemark', component: () => import('@/views/student/StudentAppealRemark.vue'), meta: { title: 'Appeal' } }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.afterEach((to) => {
  if (to.meta?.title) {
    document.title = to.meta.title;
  } else {
    document.title = 'GradeWise'; // fallback
  }
});

export default router;
