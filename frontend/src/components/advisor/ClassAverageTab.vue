<template>
  <div class="mt-3">
    <div v-if="loading" class="text-muted">Loading class average...</div>
    <canvas v-show="!loading" id="averageChart" height="120"></canvas>
  </div>
</template>


<script>
import { Chart, registerables } from 'chart.js';
import { nextTick } from 'vue'; //
import api from '@/api';

Chart.register(...registerables);

export default {
  props: ['courseId'],
  data() {
    return {
      loading: true,
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
      try {
        const res = await api.get(`/advisor/courses/${this.courseId}/class-average`);
        const raw = res.data;

        const totals = {};
        const counts = {};

        raw.forEach(row => {
          const type = row.component_type;
          const score = (parseFloat(row.obtained_mark) / parseFloat(row.max_mark)) * 100;

          if (!totals[type]) {
            totals[type] = 0;
            counts[type] = 0;
          }

          totals[type] += score;
          counts[type] += 1;
        });

        const labels = Object.keys(totals);
        const data = labels.map(type => (totals[type] / counts[type]).toFixed(2));

        await nextTick(); // Wait for canvas to be in DOM

        // Fallback delay if still fails
        setTimeout(() => {
          const ctx = document.getElementById('averageChart');
          if (ctx) {
            this.renderChart(labels, data);
          } else {
            console.warn('Canvas #averageChart not found after nextTick.');
          }
        }, 100);

      } catch (err) {
        console.error('Error fetching class average:', err);
      } finally {
        this.loading = false;
      }
    },
    renderChart(labels, data) {
      const ctx = document.getElementById('averageChart');
      if (!ctx) return;

      if (this.chart) {
        this.chart.destroy();
      }

      this.chart = new Chart(ctx, {
        type: 'bar', // or 'radar'
        data: {
          labels,
          datasets: [{
            label: 'Average Score (%)',
            data,
            backgroundColor: '#A52A2A',
            borderColor: '#A52A2A',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: 'Class Average per Assessment Component'
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
