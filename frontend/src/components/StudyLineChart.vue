<template>
  <div class="d-flex justify-content-center">
    <div class="w-100" style="max-width: 1000px;">
      <canvas id="line-chart-gradient" class="chart-canvas" height="350px"></canvas>
    </div>
  </div>
</template>

<script setup>
import { onMounted, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import api from '@/api';

Chart.register(...registerables);

let chartInstance = null;

// Gradient helper
const getGradient = (ctx, color) => {
  const gradient = ctx.createLinearGradient(0, 0, 0, 300);
  gradient.addColorStop(0, color.replace('1)', '0.4)'));
  gradient.addColorStop(1, color.replace('1)', '0)'));
  return gradient;
};

onMounted(() => {
  nextTick(async () => {
    const ctx = document.getElementById('line-chart-gradient')?.getContext('2d');
    if (!ctx) return console.error("❌ Canvas context not found");

    const studentId = localStorage.getItem('studentId');

    try {
     
      const res = await api.get(`/student/${studentId}/performance-chart`);
      const { assessments, courses } = res.data;
      console.log("API data:", res.data);

      const colors = [
        'rgba(75,192,192,1)',
        'rgba(153,102,255,1)',
        'rgba(255,159,64,1)',
        'rgba(255,99,132,1)',
        'rgba(54,162,235,1)'
      ];

      const datasets = courses.map((course, index) => {
        const color = colors[index % colors.length];
        return {
          label: course.course,
          data: course.marks,
          fill: true,
          backgroundColor: getGradient(ctx, color),
          borderColor: color,
          tension: 0.4,
        };
      });

      // Destroy existing chart before creating new
      if (chartInstance) chartInstance.destroy();

      chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
          labels: assessments,
          datasets: datasets
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'top',
            },
            title: {
              display: true,
              text: 'Performance Progress by Course'
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              suggestedMax: 100
            }
          }
        }
      });

    } catch (error) {
      console.error("Failed to fetch chart data:", error);
    }
  });
});
</script>

<style scoped>
.chart-canvas {
  width: 100%;
  height: 350px !important;
}
</style>
