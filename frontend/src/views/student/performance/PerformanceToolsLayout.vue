<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="'Performance Tools'">
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <router-link
          class="nav-link"
          :class="{ active: isActive('/student/performance/gpa') }"
          to="/student/performance/gpa"
        >
          Target GPA
        </router-link>
      </li>
      <li class="nav-item">
        <router-link
          class="nav-link"
          :class="{ active: isActive('/student/performance/cgpa') }"
          to="/student/performance/cgpa"
        >
          Cumulative GPA
        </router-link>
      </li>
      <li class="nav-item">
        <router-link
          class="nav-link"
          :class="{ active: isActive('/student/performance/what-if') }"
          to="/student/performance/what-if"
        >
          What-If Scenario
        </router-link>
      </li>
    </ul>

    <!-- GPA/CGPA/What-If child components render here -->
    <router-view />
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';

export default {
  name: 'PerformanceToolsLayout',
  components: { AppLayout },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/student/dashboard', active: false },
        { name: 'Performance Tools', link: '/student/performance', active: true },
      ]
    };
  },
  methods: {
    isActive(path) {
      return this.$route.path === path;
    }
  }
};
</script>
