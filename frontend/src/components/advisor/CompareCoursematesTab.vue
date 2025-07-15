<template>
  <div class="mt-3">
    <div v-if="loading" class="text-muted">Loading student scores...</div>
    <div v-else-if="!students.length" class="text-muted">No students found for this course.</div>
    <div v-else>
      <div v-show="chartReady" ref="chartContainer">
        <canvas id="compareChart" height="120"></canvas>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
import { nextTick } from 'vue';
import api from '@/api';

Chart.register(...registerables);

export default {
  props: ['courseId'],
  data() {
    return {
      loading: true,
      chartReady: false,
      students: [],
      chart: null
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
      this.chartReady = false;
      try {
        const res = await api.get(`/advisor/courses/${this.courseId}/comparison`, {
          headers: {
            'X-User': JSON.stringify(JSON.parse(localStorage.getItem('user')))
          }
        });

        const raw = res.data || [];
        const aggregated = {};

        raw.forEach(item => {
          const id = item.student_id;
          if (!aggregated[id]) {
            aggregated[id] = {
              name: item.student_name,
              total: 0,
              totalWeight: 0
            };
          }

          const obtained = parseFloat(item.obtained_mark);
          const max = parseFloat(item.max_mark);
          const weight = parseFloat(item.weight_percentage);

          if (!isNaN(obtained) && !isNaN(max) && !isNaN(weight) && max > 0) {
            const weightedScore = (obtained / max) * weight;
            aggregated[id].total += weightedScore;
            aggregated[id].totalWeight += weight;
          }
        });

        this.students = Object.values(aggregated).map(s => ({
          name: s.name,
          total: s.totalWeight > 0 ? parseFloat(((s.total / s.totalWeight) * 100).toFixed(2)) : 0
        }));

        this.chartReady = true;

        await nextTick();

        setTimeout(() => {
          const ctx = document.getElementById('compareChart');
          if (ctx) {
            this.renderChart();
          } else {
            console.warn('Canvas still not found after delay.');
          }
        }, 100);

        if (this.$refs.chartContainer && this.$refs.chartContainer.querySelector('#compareChart')) {
          this.renderChart();
        } else {
          console.warn('Canvas element not found even after nextTick.');
        }

      } catch (err) {
        console.error('Error fetching student scores:', err);
      } finally {
        this.loading = false;
      }
    },
    renderChart() {
      const ctx = document.getElementById('compareChart');
      if (!ctx) {
        console.warn('compareChart canvas not found');
        return;
      }

      if (this.chart) {
        this.chart.destroy();
      }

      const labels = this.students.map(s => s.name);
      const data = this.students.map(s => s.total);

      console.log('Rendering chart with:', labels, data);

      this.chart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Total Score (%)',
            data,
            backgroundColor: '#800000',
            borderColor: '#800000',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Compare Coursemates'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });
    }
  }
};
</script>
