<template>
  <div class="mt-3">
    <div v-if="loading" class="text-muted">Loading mark breakdown...</div>
    <div v-else>
      <table class="table table-bordered table-hover text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Matric No</th>
            <th v-for="title in assessmentTitles" :key="title">{{ title }}</th>
            <th>Total (%)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!breakdownRows.length">
            <td colspan="6" class="text-muted">No data available.</td>
          </tr>
          <tr v-for="(student, index) in breakdownRows" :key="student.matric_number">
            <td>{{ index + 1 }}</td>
            <td>{{ student.matric_number }}</td>
            <td v-for="title in assessmentTitles" :key="title">
              {{ student.scores[title] ?? '-' }}
            </td>
            <td>{{ student.total }}</td>
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
      loading: true,
      rawData: [],
      assessmentTitles: [],
    };
  },
  computed: {
    breakdownRows() {
      const studentMap = {};

      this.rawData.forEach(item => {
        const matric = item.matric_number;
        const title = item.assessment_title;
        const obtained = parseFloat(item.obtained_mark);
        const max = parseFloat(item.max_mark);
        const weight = parseFloat(item.weight_percentage);

        if (!studentMap[matric]) {
          studentMap[matric] = {
            matric_number: matric,
            scores: {},
            total: 0
          };
        }

        const percentage = (obtained / max) * weight;

        studentMap[matric].scores[title] = obtained;
        studentMap[matric].total += percentage;
      });

      return Object.values(studentMap).map(s => ({
        ...s,
        total: s.total.toFixed(2)
      }));
    }
  },
  methods: {
    async fetchBreakdown() {
      this.loading = true;
      try {
        const res = await api.get(`/advisor/courses/${this.courseId}/breakdown`);
        this.rawData = res.data || [];

        // Extract all unique assessment titles for table headers
        const titles = new Set();
        this.rawData.forEach(row => {
          titles.add(row.assessment_title);
        });

        this.assessmentTitles = Array.from(titles);
      } catch (err) {
        console.error('Error fetching breakdown:', err);
      } finally {
        this.loading = false;
      }
    }
  },
  watch: {
    courseId: {
      immediate: true,
      handler() {
        this.fetchBreakdown();
      }
    }
  }
};
</script>
