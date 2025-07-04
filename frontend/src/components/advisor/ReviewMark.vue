<template>
  <div>
    <h2 class="text-xl font-bold mb-4">Pending Reviews</h2>
    <ul>
      <li v-for="review in reviews" :key="review.id" class="mb-2 p-2 border rounded">
        <p><strong>{{ review.student_name }}</strong> - {{ review.course }}</p>
        <p>Status: {{ review.status }}</p>
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
    axios.get('http://localhost/backend/public/api/review-marks')
      .then(res => {
        this.reviews = res.data;
      });
  }
};
</script>