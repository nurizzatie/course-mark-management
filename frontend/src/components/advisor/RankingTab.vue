<template>
  <div>
    <div v-if="loading" class="text-muted">Loading ranking...</div>
    <div v-else>
      <table class="table table-bordered table-hover align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>Rank</th>
            <th>Name</th>
            <th>Matric No</th>
            <th>Total (%)</th>
            <th>Grade</th>
            <th>Remark</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!ranked.length">
            <td colspan="6" class="text-muted">No data available.</td>
          </tr>
          <tr v-for="(s, index) in ranked" :key="s.student_id">
            <td>{{ index + 1 }}</td>
            <td>{{ s.name }}</td>
            <td>{{ s.matric }}</td>
            <td>{{ s.total.toFixed(2) }}</td>
            <td>{{ s.grade }}</td>
            <td>{{ s.remarks || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script>
import api from '@/api';

export default {
  props: ['courseId'],
  data() {
    return {
      raw: [],
      ranked: [],
      loading: true
    };
  },
  watch: {
    courseId: {
      immediate: true,
      handler() {
        this.fetchData();
      }
    }
  },
  methods: {
    async fetchData() {
      this.loading = true;
      try {
        const res = await api.get(`/advisor/courses/${this.courseId}/ranking`);
        this.raw = res.data;

        const grouped = {};

        this.raw.forEach(row => {
          const id = row.student_id;
          const max = parseFloat(row.max_mark);
          const obtained = parseFloat(row.obtained_mark);
          const weight = parseFloat(row.weight_percentage);
          const score = (obtained / max) * weight;

          if (!grouped[id]) {
            grouped[id] = {
              student_id: id,
              name: row.student_name,
              matric: row.matric_number,
              remarks: row.remarks,
              total: 0
            };
          }

          grouped[id].total += score;
        });

        // Convert to array, sort by total descending, add grade
        const sorted = Object.values(grouped)
          .map(s => ({
            ...s,
            grade: this.getGrade(s.total)
          }))
          .sort((a, b) => b.total - a.total);

        this.ranked = sorted;
      } catch (err) {
        console.error('Error fetching ranking:', err);
      } finally {
        this.loading = false;
      }
    },
    getGrade(total) {
      if (total >= 90) return 'A+';
      if (total >= 80) return 'A';
      if (total >= 75) return 'A-';
      if (total >= 70) return 'B+';
      if (total >= 65) return 'B';
      if (total >= 60) return 'B-';
      if (total >= 55) return 'C+';
      if (total >= 50) return 'C';
      if (total >= 45) return 'C-';
      if (total >= 40) return 'D+';
      if (total >= 35) return 'D';
      if (total >= 30) return 'D-';
      return 'E';
    }
  }
};
</script>
