<template>
  <div class="p-6 max-w-4xl mx-auto">
    <p style="color: green;">AdvisorStudentList.vue is loaded</p>

    <!-- Back button here -->
    <nav class="mb-4">
      <button
        @click="goBackToDashboard"
        class="bg-yellow-400 hover:bg-yellow-500 px-4 py-2 rounded"
      >
        ⬅ Back To AdvisorDashboard
      </button>
    </nav>

    <!-- Student List -->
    <StudentList />
  </div>
</template>

<script>
import StudentList from '@/components/StudentList.vue';

export default {
  name: 'AdvisorStudentList',
  components: {
    StudentList
  },
  methods: {
    goBackToDashboard() {
      this.$router.push({ name: 'AdvisorDashboard' });
    }
  },

  mounted() {
  console.log('AdvisorStudentList.vue is mounted');
}

};
</script>





