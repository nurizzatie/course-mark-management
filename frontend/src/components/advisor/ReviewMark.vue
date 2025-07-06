<template>
  <div class="p-6 max-w-6xl mx-auto">


    <table class="w-full border border-gray-300 rounded-md">
      <thead class="bg-purple-100">
        <tr>
          <th class="px-4 py-2 text-left">Student Name</th>
          <th class="px-4 py-2 text-left">Matric No</th>
          <th class="px-4 py-2 text-left">Assessment</th>
          <th class="px-4 py-2 text-left">Mark</th>
          <th class="px-4 py-2 text-left">Max Mark</th>
          <th class="px-4 py-2 text-left">Last Updated</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="review in reviews" :key="`${review.student_id}-${review.assessment_name}`" class="border-t">
          <td class="px-4 py-2">{{ review.student_name }}</td>
          <td class="px-4 py-2">{{ review.matric_number }}</td>
          <td class="px-4 py-2">{{ review.assessment_name }}</td>
          <td class="px-4 py-2">{{ review.obtained_mark }}</td>
          <td class="px-4 py-2">{{ review.max_mark }}</td>
          <td class="px-4 py-2 text-gray-600">{{ formatDate(review.updated_at) }}</td>
        </tr>
      </tbody>
    </table>

    <p v-if="!reviews.length" class="text-gray-500 mt-4">No assessment marks available yet.</p>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ReviewMark',
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
table {
  border-collapse: collapse;
}
th {
  text-align: left;
}
</style>


