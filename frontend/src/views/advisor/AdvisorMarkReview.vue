<template>
  <AppLayout :role="'Advisor'" :navItems="navItems" :pageTitle="'Mark Review'">
    <div class="container py-4">
      <h5 class="fw-bold mb-3">📋 Mark Review</h5>

      <!-- Course Dropdown -->
      <div class="mb-4">
        <label class="form-label fw-bold">Select Course:</label>
        <select class="form-select" v-model="selectedCourse">
          <option disabled value="">-- Choose Course --</option>
          <option v-for="course in courseList" :key="course.id" :value="course.id">
            {{ course.course_name }}
          </option>
        </select>
      </div>

      <!-- Tab Navigation -->
      <ul class="nav nav-tabs mb-3">
        <li class="nav-item" v-for="(tab, idx) in tabs" :key="idx">
          <button class="nav-link" :class="{ active: activeTab === tab.key }"
                  @click="activeTab = tab.key">
            {{ tab.label }}
          </button>
        </li>
      </ul>

      <!-- Tab Content -->
      <div v-if="selectedCourse">
        <component
          :is="tabComponent"
          :course-id="selectedCourse"
          v-if="activeTab && selectedCourse"
        />
      </div>
      <div v-else class="text-muted">Please select a course to begin.</div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import FullBreakdownTab from '@/components/advisor/FullBreakdownTab.vue';
import CompareCoursematesTab from '@/components/advisor/CompareCoursematesTab.vue';
import RankingTab from '@/components/advisor/RankingTab.vue';
import ClassAverageTab from '@/components/advisor/ClassAverageTab.vue';
import api from '@/api';

export default {
  name: 'AdvisorMarkReview',
  components: {
    AppLayout,
    FullBreakdownTab,
    CompareCoursematesTab,
    RankingTab,
    ClassAverageTab
  },
  data() {
    return {
      user: JSON.parse(localStorage.getItem('user')),
      selectedCourse: '',
      courseList: [],
      activeTab: 'full',
      tabs: [
        { key: 'full', label: 'Full Mark Breakdown' },
        { key: 'rank', label: 'Ranking / Position' },
        { key: 'compare', label: 'Compare with Coursemates' },
        { key: 'average', label: 'Class Average' }
      ],
      navItems: [
        { name: 'Dashboard', link: '/advisor/dashboard' },
        { name: 'Advisees', link: '/advisor/students' },
        { name: 'Mark Review', link: '/advisor/reviews' },
        { name: 'Performance Analytics', link: '/advisor/analytics' },
        { name: 'Consultation', link: '/advisor/notes' },
        { name: 'Profile', link: '/advisor/profile' }
      ]
    };
  },
  computed: {
    tabComponent() {
      switch (this.activeTab) {
        case 'rank': return 'RankingTab';
        case 'compare': return 'CompareCoursematesTab';
        case 'average': return 'ClassAverageTab';
        default: return 'FullBreakdownTab';
      }
    }
  },
  mounted() {
    this.fetchAllCourses();
  },
  methods: {
    async fetchAllCourses() {
      try {
        const res = await api.get('/courses');
        this.courseList = res.data;
        if (this.courseList.length) {
          this.selectedCourse = this.courseList[0].id;
        }
      } catch (err) {
        console.error('Failed to fetch course list:', err);
      }
    }
  }
};
</script>
