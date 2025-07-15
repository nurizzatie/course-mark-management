<template>
  <div class="container">

    <!--  Summary Section -->
    <div
      class="d-flex align-items-center flex-wrap gap-4"
      style="max-width: 700px; margin: 0 auto;"
    >
      <!--  Percentile Circle -->
      <div class="text-center">
        <div class="circle-progress-wrapper">
          <svg class="circle" width="220" height="220">
            <circle class="circle-bg" cx="110" cy="110" r="100" stroke-width="14" fill="none" />
            <circle
              class="circle-fg"
              cx="110"
              cy="110"
              r="100"
              stroke-width="14"
              fill="none"
              :stroke-dasharray="circumference"
              :stroke-dashoffset="dashOffset"
            />
            <text
              x="50%"
              y="52%"
              text-anchor="middle"
              font-size="28"
              fill="#000"
              dy=".3em"
            >
              {{ percentile }}%
            </text>
          </svg>
        </div>
      </div>

      <!--  Text Info -->
      <div>
        <h5 class="mb-3">
          <strong> Your Class Performance </strong>
    
        </h5>
        <p class="mb-2">
          <strong>Rank:</strong> {{ rank }} out of {{ total }}
          <span v-if="rank == 1">🥇</span>
          <span v-else-if="rank == 2">🥈</span>
          <span v-else-if="rank == 3">🥉</span> <br>
          <strong>Percentile:</strong> {{percentile }}%
        </p>
      </div>
    </div>

    <!-- 🏆 Rank Table -->
    <h5 class="mb-3 mt-5"><strong>🏆 Class Ranking Table</strong></h5>
    <div class="table-responsive">
      <table class="table table-bordered align-middle text-center">
        <thead class="table-dark">
          <tr>
            <th>Student</th>
            <th>Percentile</th>
            <th>Assessments</th>
            <th>Rank</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="student in students"
            :key="student.student_id"
            :class="{ 'table-primary': student.is_you }"
          >
            <td>
              <span v-if="student.is_you">You</span>
              <span v-else>Anonymous</span>
            </td>
            <td>
              <div class="progress" style="height: 22px">
                <div
                  class="progress-bar bg-info"
                  role="progressbar"
                  :style="{ width: student.percentile + '%' }"
                >
                  {{ student.percentile }}%
                </div>
              </div>
            </td>
            <td class="text-start">
              <span
                v-for="a in student.assessments"
                :key="a.title"
                class="badge bg-secondary me-1 mb-1"
              >
                {{ a.title }}: {{ a.score }}/{{ a.max }}
              </span>
            </td>
            <td>
              {{ student.rank }}
              <span v-if="student.rank == 1">🥇</span>
              <span v-else-if="student.rank == 2">🥈</span>
              <span v-else-if="student.rank == 3">🥉</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</template>

<script>
export default {
  props: ["courseId"],
  data() {
    return {
      rank: null,
      percentile: 0,
      total: null,
      radius: 100,
      students: [],
    };
  },
  computed: {
    circumference() {
      return 2 * Math.PI * this.radius;
    },
    dashOffset() {
      return this.circumference * (1 - this.percentile / 100);
    },
  },
  async mounted() {
    const studentId = JSON.parse(localStorage.getItem("user")).id;

    try {
      // Individual performance
      const res = await fetch(
        `http://localhost:8080/api/student/course/${this.courseId}/rank/${studentId}`
      );
      const data = await res.json();
      this.rank = data.rank;
      this.percentile = data.percentile;
      this.total = data.total_students;

      //  Table data
      const tableRes = await fetch(
        `http://localhost:8080/api/student/course/${this.courseId}/rank-table`
      );
      const tableData = await tableRes.json();
      this.students = tableData.map((student) => ({
        ...student,
        is_you: student.student_id === studentId,
      }));
    } catch (err) {
      console.error("Failed to fetch rank or table:", err);
    }
  },
};
</script>

<style scoped>
.circle-bg {
  stroke: #eee;
}
.circle-fg {
  stroke: #0d6efd;
  transition: stroke-dashoffset 0.6s ease;
}
.circle text {
  font-weight: bold;
}
.circle-progress-wrapper {
  display: inline-block;
  position: relative;
}
.circle-bg,
.circle-fg {
  transform: rotate(-90deg);
  transform-origin: 50% 50%;
}

.container {
  max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}


</style>
