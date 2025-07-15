<template>
  <AppLayout :role="'Student'" :navItems="navItems" :pageTitle="'Appeal Remark'">
    <div class="container mt-4">
      <h2 class="mb-4">📢 Appeal Remark</h2>

      <div v-if="error" class="alert alert-danger">{{ error }}</div>
      <div v-if="message" class="alert alert-success">{{ message }}</div>

      <form @submit.prevent="submitAppeal" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
          <label class="form-label fw-bold">Course</label>
          <input type="text" class="form-control" :value="courseName + ' (' + courseCode + ')'" readonly />
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Component</label>
          <input type="text" class="form-control" :value="component" readonly />
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">
            Reason for Appeal <span class="text-danger">*</span>
          </label>
          <textarea
            v-model="justification"
            class="form-control"
            rows="4"
            placeholder="Explain clearly why you're appealing this remark decision."
            required
          ></textarea>
        </div>

        <div class="mb-3">
          <label for="supportingLink" class="form-label fw-bold">Supporting Link (Optional)</label>
          <input
            type="url"
            id="supportingLink"
            v-model="supportingLink"
            class="form-control"
            placeholder="e.g. https://drive.google.com/your-evidence-folder"
          />
          <small class="form-text text-muted">Link to supporting documents if applicable.</small>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Upload Supporting File (Optional)</label>
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
          <small class="form-text text-muted">Max file size: 5MB. Supported formats: PDF, ZIP, JPG, PNG.</small>
        </div>

        <div class="text-end">
          <button type="submit" class="btn btn-dark" :disabled="submitting">
            {{ submitting ? 'Submitting...' : 'Submit Appeal' }}
          </button>
        </div>
      </form>
      <div class="mt-3 alert alert-warning text-center" v-if="appealCount > 0">
  ⚠️ You have used {{ appealCount }} out of {{ maxAppeals }} appeal attempt{{ appealCount > 1 ? 's' : '' }} for this assessment.
</div>
    </div>

    

  </AppLayout>
  
</template>

<script>
import AppLayout from "@/layouts/AppLayout.vue";

export default {
  name: "StudentAppealRemark",
  components: { AppLayout },
  data() {
    return {
        appealCount: 0,
    maxAppeals: 2,  
      navItems: [
        { name: "Dashboard", link: "/student/dashboard" },
        { name: "Performance Tools", link: "/student/performance" },    
      ],
      courseName: this.$route.query.course_name || "",
      courseCode: this.$route.query.course_code || "",
      component: this.$route.query.component || "",
      courseId: this.$route.query.course_id,
      assessmentId: this.$route.query.assessment_id,
      justification: "",
      supportingLink: "",
      file: null,
      message: "",
      error: "",
      submitting: false,
    };
  },
  methods: {
    handleFile(event) {
      const selectedFile = event.target.files[0];
      const maxSizeBytes = 5 * 1024 * 1024;

      if (selectedFile && selectedFile.size > maxSizeBytes) {
        this.error = "File exceeds 5MB. Please upload a smaller file.";
        this.file = null;
        event.target.value = "";
      } else {
        this.file = selectedFile;
        this.error = "";
      }
    },
    async submitAppeal() {
        if (this.appealCount >= this.maxAppeals) {
  this.error = `You have already used all ${this.maxAppeals} appeal attempts.`;
  return;
}

      const student = JSON.parse(localStorage.getItem("user"));
      if (!student) {
        this.error = "You must be logged in to submit an appeal.";
        return;
      }

      if (!this.justification.trim()) {
        this.error = "Justification is required.";
        return;
      }

      this.submitting = true;
      this.error = "";
      this.message = "";

      const formData = new FormData();
      formData.append("student_id", student.id);
      formData.append("assessment_id", this.assessmentId);
      formData.append("justification", this.justification.trim());
      if (this.supportingLink.trim()) {
        formData.append("supporting_link", this.supportingLink.trim());
      }
      if (this.file) {
        formData.append("supporting_file", this.file);
      }

      try {
        const res = await fetch("http://localhost:8080/api/remark/appeal", {
          method: "POST",
          body: formData,
        });

        const result = await res.json();

        if (!res.ok) throw new Error(result.error || "Failed to submit appeal.");

        this.message = " Appeal submitted successfully!";
        this.error = "";
        this.justification = "";
        this.supportingLink = "";
        this.file = null;
        if (this.$refs.fileInput) this.$refs.fileInput.value = "";
      } catch (err) {
        this.error = err.message || "Something went wrong.";
        this.message = "";
      } finally {
        this.submitting = false;
      }
    },
  },
    mounted() {
  const student = JSON.parse(localStorage.getItem("user"));

  // Get query data first
  const query = this.$route.query;
  this.courseId = query.course_id;
  this.assessmentId = query.assessment_id;
  this.courseName = query.course_name;
  this.courseCode = query.course_code;
  this.component = query.component;

  // Now it's safe to fetch appeal count
  fetch(`http://localhost:8080/api/remark/appeal-count?student_id=${student.id}&assessment_id=${this.assessmentId}`)
    .then((res) => res.json())
    .then((data) => {
      this.appealCount = data.appeal_count || 0;
    })
    .catch((err) => {
      console.error("⚠️ Failed to fetch appeal count:", err);
    });
}

};
</script>

<style scoped>
textarea {
  resize: vertical;
}
</style>
