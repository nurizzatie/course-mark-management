<template>
  <AppLayout
    :role="'Student'"
    :navItems="navItems"
    :pageTitle="'Request Remark'"
  >
    <div class="container mt-4">
      <h2 class="mb-4">📝 Request Remark</h2>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <div v-if="message" class="alert alert-success">{{ message }}</div>

      <form
        @submit.prevent="submitRequest"
        enctype="multipart/form-data"
        class="card p-4 shadow-sm"
      >
        <div class="mb-3">
          <label class="form-label fw-bold">Course</label>
          <input
            type="text"
            class="form-control"
            :value="courseName + ' (' + courseCode + ')'"
            readonly
          />
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Component</label>
          <input type="text" class="form-control" :value="component" readonly />
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold"
            >Reason for Remark <span class="text-danger">*</span></label
          >
          <textarea
            v-model="justification"
            class="form-control"
            rows="4"
            placeholder="Explain clearly your justification here.."
            required
          ></textarea>
        </div>

        <div class="mb-3">
          <label for="supportingLink" class="form-label fw-bold"
            >Supporting Link (Optional)</label
          >
          <input
            type="url"
            id="supportingLink"
            v-model="supportingLink"
            class="form-control"
            placeholder="e.g. https://drive.google.com/your-evidence-folder"
          />
          <small class="form-text text-muted"
            >Link to any supporting documents if applicable.</small
          >
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold"
            >Upload Supporting File (Optional)</label
          >
          <input
            type="file"
            class="form-control"
            @change="handleFile"
            accept=".pdf,.zip,.jpg,.jpeg,.png"
            ref="fileInput"
          />
          <div v-if="file" class="mt-2 text-muted">
  Selected file: {{ file.name }}
</div>

          <small class="form-text text-muted">Max file size: 5MB</small><br>

          <small class="form-text text-muted"
            >Supported formats: PDF, ZIP, JPG, PNG</small
          >
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-dark">
              Submit Request
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";

export default {
  name: "RequestRemark",
  components: { AppLayout },
  data() {
    return {
      navItems: [
        { name: "Dashboard", link: "/student/dashboard", active: false },
        { name: "Performance Tools", link: "/student/performance", active: false},
      ],
      studentId: null,
      courseId: null,
      assessmentId: null,
      courseName: "",
      courseCode: "",
      component: "",
      justification: "",
      file: null,
      message: "",
      error: "",
      supportingLink: "",
      submitAttempted: false,
    };
  },
  mounted() {
    const student = JSON.parse(localStorage.getItem("user"));
    const query = this.$route.query;

    this.studentId = student?.id;
    this.courseId = query.course_id;
    this.assessmentId = query.assessment_id;
    this.courseName = query.course_name;
    this.courseCode = query.course_code;
    this.component = query.component;

  },
  methods: {
   handleFile(event) {
  const selectedFile = event.target.files[0];
  const maxSizeMB = 5;
  const maxSizeBytes = maxSizeMB * 1024 * 1024;

  if (selectedFile && selectedFile.size > maxSizeBytes) {
    this.error = `File size exceeds ${maxSizeMB}MB limit. Please upload a smaller file.`;
    this.file = null;
    event.target.value = ''; // reset file input
  } else {
    this.file = selectedFile;
    this.error = ''; // clear error if previously triggered
  }
},
    async submitRequest() {
       this.submitAttempted = true;
       this.error = "";
       this.message = "";

      if (!this.studentId || !this.courseId || !this.assessmentId) {
      this.error = "Missing required information to submit request.";
      return;
      }
      if (!this.justification.trim()) {
        this.error = "Justification is required.";
        return;
      }

      if (!this.file) {
        this.error = "You must upload a supporting file.";
        return;
      }

      const formData = new FormData();
      formData.append("student_id", this.studentId);
      formData.append("assessment_id", this.assessmentId);
      formData.append("justification", this.justification);
      if (this.file) {
        formData.append("file", this.file);
      }
      if (this.supportingLink.trim()) {
        formData.append("supporting_link", this.supportingLink.trim());
      }

      try {
        const res = await fetch(`http://localhost:8080/api/remark/request`, {
          method: "POST",
          body: formData,
        });

        if (!res.ok) throw new Error("Failed to submit request.");

        this.message = "Remark request submitted successfully!";
        this.error = "";
        this.justification = "";
        this.supportingLink = "";
        this.file = null;
        if (this.$refs.fileInput) this.$refs.fileInput.value = "";
      } catch (err) {
        console.error(err);
        this.error = "Something went wrong. Please try again.";
        this.message = "";
      }
    },
  },
};
</script>
