<template>
 <!-- Collapsible Instructions -->
<div class="mb-4">
  <button
    class="btn btn-primary"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#gradePredictorInstructions"
    aria-expanded="false"
    aria-controls="gradePredictorInstructions"
  >
    📘 How to Use Grade Predictor
  </button>

  <div class="collapse mt-3" id="gradePredictorInstructions">
    <div class="card card-body">
      <h5 class="mb-3">🎯 Grade Predictor Tool</h5>
      <p><strong>Simulate your future academic performance.</strong></p>
      <ul>
        <li>Forecast your semester or cumulative GPA.</li>
        <li>Strategize which subjects need focus to reach your target.</li>
        <li>Test different grade combinations for better academic planning.</li>
      </ul>
      <p class="mt-3"><strong>📝 Note:</strong> Confirmed grades from lecturers are <span class="text-danger">locked</span> and cannot be changed. You can only modify grades for courses that are still pending or ungraded.</p>
    </div>
  </div>
</div>

</template>

<script>
export default {
  name: 'GradePredictor',
  data() {
    return {
      // Add your future logic and states here
    };
  },
  methods: {
    // Add future methods here
  },
  mounted() {
    // Fetch course data if needed later
  }
};
</script>

<style scoped>
.grade-predictor {
  max-width: 100%;
  margin: auto;
}
</style>
