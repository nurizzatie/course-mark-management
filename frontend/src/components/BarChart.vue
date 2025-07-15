<template>
  <Bar :data="chartData" :options="chartOptions" />
</template>

<script>
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar } from 'vue-chartjs'

// Register Chart.js modules
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

export default {
  name: 'BarChart',
  components: { Bar },
  props: {
    chartTitle: {
    type: String,
    required: true
  },
    chartData: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      chartOptions: {
        responsive: true,
        plugins: {
          legend: {
            display: false,
            position: 'top'
          },
          title: {
            display: true,
            text: this.chartTitle
          }
        },
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    }
  }
}
</script>
