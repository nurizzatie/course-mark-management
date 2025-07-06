<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="'My Course'">
    <!-- Navigation Tabs -->
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item">
        <router-link
          class="nav-link"
          :class="{ active: isActive('/student/') }"
          :to="`/student/course/${courseId}`"
        >
          Mark Breakdown
        </router-link>
      </li>
      <li class="nav-item">
        <router-link
          class="nav-link"
          :class="{ active: isActive('compare') }"
          :to="`/student/course/${courseId}/compare`"
        >
          Compare Marks
        </router-link>
      </li>

      <li class="nav-item">
  <router-link
    class="nav-link"
    :class="{ active: isActive('rank') }"
    :to="`/student/course/${courseId}/rank`"
  >
    Rank & Percentile
  </router-link>
</li>

    </ul>

    <!-- Child route content -->
    <router-view :course-id="courseId" />
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'

export default {
  name: 'CourseMarksLayout',
  components: { AppLayout },
  computed: {
    courseId() {
      return this.$route.params.id
    }
  },
  data() {
    return {
      navItems: [
        { name: 'Dashboard', link: '/student/dashboard', active: false },
        { name: 'My Courses', link: '/student/courses', active: true },
        { name: 'Performance Tools', link: '/student/performance', active: false },
        { name: 'Notifications', link: '/student/notifications', active: false },
        { name: 'Profile', link: '/student/profile', active: false }
      ]
    }
  },
  methods: {
   isActive(tab) {
  const path = this.$route.path
  const base = `/student/course/${this.courseId}`

  if (tab === 'compare') {
    return path === `${base}/compare`
  }
  if (tab === 'rank') {
    return path === `${base}/rank`
  }
  return path === base
}

  }
}
</script>

