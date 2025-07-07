<template>
  <div class="compare-marks">
    <div class="container mt-4">
      <h2 class="mb-4">
        <strong
          >Mark Comparison for – {{ course.name }} {{ course.code }}</strong
        >
      </h2>

      <!-- Assessment Dropdown -->
      <div class="mb-3 row align-items-center">
        <label class="col-sm-1 col-form-label">Assessment:</label>
        <div class="col-sm-3">
          <select
            class="form-select"
            v-model="selectedAssessmentId"
            @change="fetchComparisonData"
          >
            <option v-for="a in assessments" :key="a.id" :value="a.id">
              {{ a.title }}
            </option>
          </select>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="chart-section">
        <div v-if="chartData" class="mb-4">
          <div class="d-flex justify-content-center">
            <div style="max-width: 700px; width: 100%">
              <BarChart
                v-if="chartData.labels && chartData.labels.length"
                :chart-data="chartData"
              />
              <p v-else>No chart data found for this assessment.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Toggle Table -->
      <div class="text-end mb-3">
        <button @click="toggleTable" class="btn btn-dark">
          {{ showTable ? "Hide Table" : "Show Table" }}
        </button>
      </div>

      <!-- Table Section -->
      <div v-if="showTable" class="table-responsive">
        <h4 class="mb-3"><strong>Mark Comparison Table</strong></h4>
        <table class="table table-bordered table-hover align-middle">
          <thead class="table-dark">
            <tr>
              <th>Student</th>
              <th>Mark</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="data in comparisonData"
              :key="data.student_label"
              :class="{ 'table-success': data.student_label === 'You' }"
            >
              <td>{{ data.student_label }}</td>
              <td>{{ data.mark }}</td>
            </tr>

            <tr class="table-warning fw-bold text-center">
              <td class ="text-end">📊 Class Average</td>
              <td class ="text-start">{{ classAverage }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import BarChart from "@/components/BarChart.vue";

export default {
  name: "CompareMarks",
  components: { BarChart },
  data() {
    return {
      course: {
        name: "",
        code: "",
      },
      assessments: [],
      selectedAssessmentId: null,
      comparisonData: [],
      chartData: null,
      classAverage: null,
      showTable: false,
    };
  },
  computed: {
    courseId() {
      return this.$route.params.id;
    },
  },
  methods: {
    toggleTable() {
      this.showTable = !this.showTable;
    },
    async fetchCourseInfo() {
      try {
        const res = await fetch(
          `http://localhost:8080/api/course/${this.courseId}`
        );
        const data = await res.json();
        this.course = data;
      } catch (err) {
        console.error("Error fetching course info:", err);
      }
    },
    async fetchAssessments() {
  try {
    const res = await fetch(
      `http://localhost:8080/api/student/course/${this.courseId}/assessment-list`
    );
    const allAssessments = await res.json();

    const student = JSON.parse(localStorage.getItem("user")) || { id: 1 };

    const gradedAssessments = [];

    for (const a of allAssessments) {
      const res = await fetch(
        `http://localhost:8080/api/student/course/${this.courseId}/compare/${a.id}?student_id=${student.id}`
      );
      const resData = await res.json();

      const isGraded = resData.data?.some(
        (d) => d.total_contribution !== null && d.total_contribution > 0
      );

      if (isGraded) {
        gradedAssessments.push(a);
      }
    }

    this.assessments = gradedAssessments;

    if (this.assessments.length > 0) {
      this.selectedAssessmentId = this.assessments[0].id;
      await this.fetchComparisonData();
    } else {
      this.selectedAssessmentId = null;
      this.chartData = null;
      this.comparisonData = [];
      this.classAverage = null;
    }
  } catch (err) {
    console.error("Error fetching assessments or grading info:", err);
  }
},
    async fetchComparisonData() {
      if (!this.selectedAssessmentId) return;

      try {
        const student = JSON.parse(localStorage.getItem("user")) || { id: 1 };
        const res = await fetch(
          `http://localhost:8080/api/student/course/${this.courseId}/compare/${this.selectedAssessmentId}?student_id=${student.id}`
        );
        const resData = await res.json();

        if (
          !resData ||
          !Array.isArray(resData.data) ||
          resData.data.length === 0
        ) {
          this.chartData = null;
          return;
        }

        this.comparisonData = resData.data.map((d) => ({
          student_label: d.student_label,
          mark: d.total_contribution,
        }));

        this.classAverage = resData.class_average;

        this.chartData = {
          labels: resData.data.map((d) => d.student_label),
          datasets: [
            {
              label: "You",
              backgroundColor: resData.data.map((d) =>
                d.student_label === "You" ? "#4ade80" : "#93c5fd"
              ),
              data: resData.data.map((d) => Number(d.total_contribution)),
            },
          ],
        };
      } catch (err) {
        console.error("Error fetching comparison data:", err);
        this.chartData = null;
      }
    },
  },
  async mounted() {
    await this.fetchCourseInfo();
    await this.fetchAssessments();
  },
};
</script>

<style scoped>
.table-success td {
  font-weight: bold;
}

.compare-marks {
  max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.chart-section {
  max-width: 700px;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
