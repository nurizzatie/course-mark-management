<template>
  <canvas ref="canvas"></canvas>
</template>

<script>
import { Chart } from 'chart.js/auto';

export default {
  name: 'BarChart',
  props: ['chartData', 'chartOptions'],
  mounted() {
    new Chart(this.$refs.canvas, {
      type: 'bar',
      data: this.chartData,
      options: this.chartOptions
    });
  }
};
</script>
