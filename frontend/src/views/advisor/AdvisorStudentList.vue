<template>
  <div class="p-6 max-w-5xl mx-auto">
    <p class="text-green-700 font-medium mb-2">AdvisorStudentList.vue is loaded</p>

    <!-- Back button -->
    <nav class="mb-4">
      <button
        @click="goBackToDashboard"
        class="bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded"
      >
        ⬅ Back To AdvisorDashboard
      </button>
    </nav>

    <!-- Student List Component -->
    <StudentList />
  </div>
</template>

<script>
import StudentList from '@/components/advisor/StudentList.vue';

export default {
  name: 'AdvisorStudentList',
  components: { StudentList },
  methods: {
    goBackToDashboard() {
      this.$router.push({ name: 'AdvisorDashboard' });
    }
  }
};
</script>






