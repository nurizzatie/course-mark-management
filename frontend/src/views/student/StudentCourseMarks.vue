<template>
 <div class="marks-breakdown">
    <div class="container mt-4">
      <div v-if="loading">Loading course marks...</div>
      <div v-else-if="error" class="text-danger">{{ error }}</div>
      <div v-else>
        <h2><strong>{{ courseName }} ({{ courseCode }})</strong></h2>
        <p class="text-muted mb-4">Component-wise Marks</p>

        <table class="table table-striped" v-if="marks.length > 0">
          <thead>
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
  <button
  :class="['btn btn-sm', item.remark_status ? 'btn-secondary' : 'btn-outline-primary']"
  :disabled="item.remark_status"
  @click="!item.remark_status && requestRemark(item)"
>
  {{ item.remark_status ? 'Remark Submitted' : 'Request Remark' }}
</button>


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
      navItems: [
        { name: "Dashboard", link: "/student/dashboard", active: false },
        { name: "My Courses", link: "/student/courses", active: true },
        {
          name: "Performance Tools",
          link: "/student/performance",
          active: false,
        },
        {
          name: "Notifications",
          link: "/student/notifications",
          active: false,
        },
        { name: "Profile", link: "/student/profile", active: false },
      ],
    };
  },
  async mounted() {
    const student = JSON.parse(localStorage.getItem("user"));
    const courseId = this.$route.params.id;

    if (!student || !courseId) {
      this.error = "Missing student or course ID.";
      this.loading = false;
      return;
    }

    try {
      const res = await fetch(
        `http://localhost:8080/api/student/${student.id}/course/${courseId}/marks`
      );
      const data = await res.json();

      // Calculate contribution for each component
      this.marks = data.components.map((item) => {
        const obtained = item.obtained_mark ?? 0;
        const max = item.max_mark ?? 0;
        const weight = item.weight_percentage ?? 0;

        const contribution =
          max > 0 ? ((obtained / max) * weight).toFixed(2) : "0.00";

        return {
          ...item,
          contribution,
        };
      });

      this.totalObtained = data.summary.total_obtained;
      this.totalMax = data.summary.total_max;
      this.percentage = data.summary.percentage;

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
  methods: {
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

