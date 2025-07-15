<template>
  <div class="marks-breakdown">
    <div class="container mt-4">
      <div v-if="loading">Loading course marks...</div>
      <div v-else-if="error" class="text-danger">{{ error }}</div>
      <div v-else>
        <h2>
          <strong>{{ courseName }} ({{ courseCode }})</strong>
        </h2>
        <p class="text-muted mb-4">Lecturer: {{ lecturerName }}</p>
        <p class="text-muted mb-4">Component-wise Marks</p>

        <table class="table table-light table-striped" v-if="marks.length > 0">
          <thead class="table-dark">
            <tr>
              <th>Component</th>
              <th>Max Mark</th>
              <th>Obtained</th>
              <th>Weight (%)</th>
              <th>Contribution (%)</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in marks" :key="item.assessment_id">
              <td>{{ item.component }}</td>
              <td>{{ item.max_mark }}</td>
              <td>
                {{ item.obtained_mark !== null ? item.obtained_mark : "-" }}
              </td>
              <td>{{ item.weight_percentage }}</td>
              <td>{{ item.contribution }}</td>
              <td>
                <!-- No remark yet -->
                <button
                  v-if="!item.remark_status"
                  class="btn btn-outline-dark btn-sm"
                  @click="requestRemark(item)"
                  :disabled="!item.obtained_mark"
                >
                  Request Remark
                </button>

                <!-- Pending status -->
                <span
                  v-else-if="item.remark_status === 'pending'"
                  class="badge bg-warning text-dark rounded-pill"
                >
                  Pending
                </span>

                <!-- Approved status -->
                <span
                  v-else-if="item.remark_status === 'approved'"
                  class="badge badge-pill bg-success"
                >
                  Approved
                </span>

                <!-- Rejected with Appeal button -->
                <div
                  v-else-if="item.remark_status === 'rejected'"
                  class="d-flex gap-2 align-items-center"
                >
                  <span class="badge badge-pill bg-danger">Rejected</span>

                  <button
                    v-if="item.appealCount < 2"
                    class="btn btn-sm btn-danger"
                    @click="appealRemark(item)"
                  >
                    Appeal
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <div v-else>
          <p class="text-muted">No assessments found for this course.</p>
        </div>

        <div class="mt-4">
          <h5>
            Total: {{ totalObtained }} / {{ totalMax }} ({{ percentage }}%)
          </h5>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '@/api';

export default {
  name: "CourseMarks",
  data() {
    return {
      courseName: "",
      courseCode: "",
      marks: [],
      totalObtained: 0,
      totalMax: 0,
      percentage: 0,
      loading: true,
      error: null,
      lecturerName: "",
    };
  },
  async mounted() {
    await this.fetchMarks();
  },
  watch: {
    $route() {
      this.fetchMarks();
    },
  },
  methods: {
    async fetchMarks() {
      const student = JSON.parse(localStorage.getItem("user"));
      const courseId = this.$route.params.id;

      if (!student || !courseId) {
        this.error = "Missing student or course ID.";
        this.loading = false;
        return;
      }

      try {
        this.loading = true;
        const res = await api.get(
          `/student/${student.id}/course/${courseId}/marks`
        );
       const data =  await res.data;

        // Fix: await Promise.all for async inside map
        this.marks = await Promise.all(
          data.components.map(async (item) => {
            const obtained = item.obtained_mark ?? 0;
            const max = item.max_mark ?? 0;
            const weight = item.weight_percentage ?? 0;
            const contribution =
              max > 0 ? ((obtained / max) * weight).toFixed(2) : "0.00";

            let appealCount = 0;
            if (item.remark_status === "rejected") {
              appealCount = await this.fetchAppealCount(
                student.id,
                item.assessment_id
              );
            }

            return {
              ...item,
              contribution,
              appealCount,
            };
          })
        );

        this.totalObtained = data.summary.total_obtained;
        this.totalMax = data.summary.total_max;
        this.percentage = data.summary.percentage;
        this.lecturerName = data.components[0]?.lecturer_name || "";

        if (data.components.length > 0) {
          this.courseName = data.components[0].course_name || "Unknown Course";
          this.courseCode = data.components[0].course_code || "N/A";
        } else {
          this.courseName = "Unknown Course";
          this.courseCode = "N/A";
        }
      } catch (err) {
        console.error(err);
        this.error = "Failed to load course marks.";
      } finally {
        this.loading = false;
      }
    },

    async fetchAppealCount(studentId, assessmentId) {
      try {
        const res = await api.get(
          `/remark/appeal-count?student_id=${studentId}&assessment_id=${assessmentId}`
        );
        const data = res.data;
        return data.appeal_count || 0;
      } catch (err) {
        console.error("Failed to fetch appeal count:", err);
        return 0;
      }
    },

    requestRemark(item) {
      this.$router.push({
        name: "RequestRemark",
        query: {
          course_name: this.courseName,
          course_code: this.courseCode,
          component: item.component,
          course_id: item.course_id,
          assessment_id: item.assessment_id,
        },
      });
    },

    appealRemark(item) {
      this.$router.push({
        name: "AppealRemark",
        query: {
          course_name: this.courseName,
          course_code: this.courseCode,
          component: item.component,
          course_id: item.course_id,
          assessment_id: item.assessment_id,
        },
      });
    },
  },
};
</script>

<style scoped>
.marks-breakdown {
  max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
