<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold mb-4">Student List</h2>
    <StudentList />
  </div>
</template>

<script>
import StudentList from '@/components/advisor/StudentList.vue'; // ✅ Correct path now

export default {
  name: 'AdvisorStudentList',
  components: {
    StudentList
  }
};
</script>

