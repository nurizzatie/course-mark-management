<template>
  <canvas ref="canvas"></canvas>
</template>

<script>
import { Chart } from 'chart.js/auto';

export default {
  name: 'LineChart',
  props: ['chartData', 'chartOptions'],
  mounted() {
    new Chart(this.$refs.canvas, {
      type: 'line',
      data: this.chartData,
      options: this.chartOptions
    });
  }
};
</script>
