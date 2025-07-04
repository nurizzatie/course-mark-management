<template>
  <div class="grade-predictor">


    <!-- 🎓 Course Selector -->
    <div class="mb-3">
      <label for="courseSelect" class="form-label fw-bold">🎓 Select Course:</label>
      <select v-model="selectedCourse" class="form-select" id="courseSelect">
        <option disabled value="">-- Choose a Course --</option>
        <option v-for="course in courses" :key="course.id" :value="course.id">
          {{ course.course_name }} ({{ course.course_code }})
        </option>
      </select>
    </div>

    <!-- 📋 Assessments Table -->
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Component</th>
          <th>Weight (%)</th>
          <th>Max Mark</th>
          <th>Obtained / Predicted</th>
        </tr>
      </thead>
      <tbody>
  <tr v-for="a in assessments" :key="a.id">
    <td>{{ a.title }}</td>
    <td>{{ a.weight_percentage }}</td>
    <td>{{ a.max_mark }}</td>
    <td>
      <div class="position-relative">
        <template v-if="a.canPredict">
          <input
            type="number"
            class="form-control"
            v-model.number="a.predicted_mark"
            :max="a.max_mark"
            min="0"
            :class="{ 'is-invalid': a.predicted_mark > a.max_mark }"
          />
          <div v-if="a.predicted_mark > a.max_mark" class="invalid-feedback">
            Cannot exceed {{ a.max_mark }}
          </div>
        </template>

        <template v-else>
          <span class="text-success fw-bold">{{ a.obtained_mark }}</span>
        </template>
      </div>
    </td>
  </tr>
</tbody>

    </table>

   <!-- 🔽 Buttons Row -->
<div class="d-flex gap-2 mb-3 flex-wrap">
  <!-- How It Works Button -->
  <button
    class="btn btn-primary btn-sm"
    type="button"
    data-bs-toggle="collapse"
    data-bs-target="#gradePredictorInstructions"
    aria-expanded="false"
    aria-controls="gradePredictorInstructions"
  >
    📘 How It Works
  </button>

  <!-- Predict Button -->
  <button class="btn btn-success btn-sm" @click="predictGrade">
    🎯 Predict
  </button>
</div>

<!-- 🔽 Collapsible Instructions -->
<div class="collapse mt-3" id="gradePredictorInstructions">
  <div class="card card-body">
    <h5 class="mb-3">🎯 Grade Predictor Tool</h5>
    <p><strong>Simulate your future academic performance.</strong></p>
    <ul>
      <li>Forecast your semester or cumulative GPA.</li>
      <li>Strategize which subjects need focus to reach your target.</li>
      <li>Test different grade combinations for better academic planning.</li>
    </ul>
    <p class="mt-3">
      <strong>📝 Note:</strong> Confirmed grades from lecturers are
      <span class="text-danger">locked</span> and cannot be changed.
      You can only modify grades for courses that are still pending or ungraded.
    </p>
  </div>
</div>

<!-- 📊 Prediction Result -->
<div v-if="predictionResult" class="alert alert-info mt-3">
  <strong>Predicted Percentage:</strong> {{ predictionResult.predicted_percentage }}% <br />
  <strong>Predicted Grade:</strong> {{ predictionResult.predicted_grade }} <br />
  <strong>Predicted CGPA:</strong> {{ predictionResult.predicted_cgpa }}
</div>
</div>
</template>

<script>
export default {
  name: 'GradePredictor',
  data() {
    return {
      selectedCourse: '',
      courses: [],
      assessments: [],
      predictionResult: null,
      studentId: null
    };
  },
  methods: {
    async fetchCourses() {
      try {
        const user = JSON.parse(localStorage.getItem('user'));
        if (!user?.id) {
          alert("Login required");
          return;
        }
        this.studentId = user.id;

        const res = await fetch(`http://localhost:8080/api/student/${this.studentId}/courses`);
        const data = await res.json();
        this.courses = data;
        console.log("✅ Courses loaded:", this.courses);
      } catch (e) {
        console.error('❌ Failed to fetch courses:', e);
        alert("Could not load courses.");
      }
    },

    async fetchAssessments() {
      if (!this.selectedCourse || !this.studentId) {
        alert("Course or student not selected properly.");
        return;
      }

      try {
        const res = await fetch(
          `http://localhost:8080/api/course/${this.selectedCourse}/assessments/with-student/${this.studentId}`
        );
        const data = await res.json();
        console.log("✅ Assessments received:", data);

        this.assessments = data.map(a => ({
          ...a,
          canPredict: a.obtained_mark === null,
          predicted_mark: a.obtained_mark || ''
        }));

        const allMarked = this.assessments.every(a => a.obtained_mark !== null);
        if (allMarked) {
          this.predictGrade(); // Auto-predict if all marks available
        }

        this.predictionResult = null; // Reset result
      } catch (e) {
        console.error("❌ Failed to fetch assessments:", e);
        alert("Unable to load assessments.");
      }
    },

    async predictGrade() {

  const hasInvalidInput = this.assessments.some(
    a => a.canPredict && a.predicted_mark > a.max_mark
  );

  if (hasInvalidInput) {
    alert("One or more predicted marks exceed the maximum allowed.");
    return;
  }

  const predicted_scores = this.assessments
    .filter(a => a.canPredict && a.predicted_mark !== '')
    .map(a => ({
      assessment_id: a.id,
      expected_mark: a.predicted_mark
    }));

  const body = {
    student_id: this.studentId,
    course_id: this.selectedCourse,
    predicted_scores
  };

  try {
    const res = await fetch('http://localhost:8080/api/grade-predictor', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    });

    const result = await res.json();
    if (res.ok) {
      this.predictionResult = result;
      console.log("📊 Prediction result:", result);
    } else {
      this.predictionResult = null;
      alert(result.error || "Prediction failed.");
    }
  } catch (e) {
    console.error("❌ Prediction error:", e);
    alert("Prediction failed.");
  }
}

  },
  watch: {
    selectedCourse(newCourseId) {
      if (newCourseId) {
        this.fetchAssessments();
      }
    }
  },
  mounted() {
    this.fetchCourses();
  }
};
</script>

<style scoped>
.grade-predictor {
 max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
