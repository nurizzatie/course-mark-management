<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Student Assessment Marks</h2>
    <ul>
      <li v-for="review in reviews" :key="`${review.student_id}-${review.assessment_name}`" class="mb-2 p-2 border rounded">
        <p><strong>{{ review.student_name }} ({{ review.matric_number }})</strong></p>
        <p>Assessment: {{ review.assessment_name }}</p>
        <p>Mark: {{ review.obtained_mark }} / {{ review.max_mark }}</p>
        <p><small>Last Updated: {{ formatDate(review.updated_at) }}</small></p>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      reviews: []
    };
  },
  mounted() {
    axios.get('http://localhost:8080/api/advisor/marks')
      .then(res => {
        this.reviews = res.data;
      })
      .catch(err => {
        console.error('Failed to load marks:', err);
      });
  },
  methods: {
    formatDate(datetime) {
      return new Date(datetime).toLocaleString();
    }
  }
};
</script>

<style scoped>
h2 {
  color: #5d001d;
}
</style>
