<template>
  <div class="cgpa-tool">
    <h2 class="mb-3">Cumulative GPA Calculator</h2>

    <!-- Toggle for optional course description -->
    <div class="form-check mb-3">
      <input
        type="checkbox"
        id="showDescription"
        class="form-check-input"
        v-model="showDescription"
      />
      <label class="form-check-label" for="showDescription"
        >Show Course Description fields</label
      >
    </div>

    <!-- Course rows -->
    <form @submit.prevent="calculateCGPA">
      <div
        v-for="(course, index) in courses"
        :key="index"
        class="course-input d-flex align-items-center gap-2 mb-2"
      >
        
          <input
            v-model="course.name"
            class="form-control"
            placeholder="Course Name"
          />
       
          <input
            v-model.number="course.credit"
            type="number"
            min="1"
            class="form-control"
            placeholder="Credit"
          />
        
      
          <select v-model="course.grade" class="form-select">
            <option disabled value="">Grade</option>
            <option v-for="(value, key) in gradeMap" :key="key" :value="key">
              {{ key }}
            </option>
          </select>
        
        <div class="col-md-4" v-if="showDescription">
          <input
            v-model="course.description"
            class="form-control"
            placeholder="Course Description (optional)"
          />
        </div>
        
          <button
          type="button"
          class="btn btn-sm btn-outline-danger"
          @click="removeCourse(index)"
        >
          ✕
        </button>
       
      </div>

      <button
        type="button"
        class="btn btn-secondary me-2"
        @click="addCourse"
        :disabled="courses.length >= 50"
      >
        + Add Row
      </button>

      <!-- Prior CGPA Section -->
      <div class="my-4">
        <h5>Prior Cumulative GPA</h5>
        <div class="row g-2">
          <div class="col-md-3">
            <input
              v-model.number="priorGPA"
              type="number"
              step="0.01"
              class="form-control"
              placeholder="Prior GPA"
            />
          </div>
          <div class="col-md-3">
            <input
              v-model.number="priorCredits"
              type="number"
              min="0"
              class="form-control"
              placeholder="Earned Credits"
            />
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" :disabled="!isFormValid">
        Calculate
      </button>
    </form>

    <!-- Results -->
    <div v-if="sgpa !== null && cgpa !== null" class="alert alert-success mt-4">
      <p><strong>Total Current Credits:</strong> {{ currentCredits }}</p>
      <p><strong>Semester GPA:</strong> {{ sgpa.toFixed(2) }}</p>
      <p><strong>Cumulative GPA:</strong> {{ cgpa.toFixed(2) }}</p>
    </div>
  </div>
</template>

<script>
export default {
  name: "CumulativeGPA",
  data() {
    return {
      courses: [{ name: "", credit: "", grade: "", description: "" }],
      showDescription: false,
      priorGPA: "",
      priorCredits: "",
      sgpa: null,
      cgpa: null,
      gradeMap: {
        "A+": 4.0,
        A: 4.0,
        "A-": 3.67,
        "B+": 3.33,
        B: 3.0,
        "B-": 2.67,
        "C+": 2.33,
        C: 2.0,
        "C-": 1.67,
        "D+": 1.33,
        D: 1.0,
        "D-": 0.67,
        E: 0.0,
      },
    };
  },
  computed: {
    isFormValid() {
      return (
        this.courses.some((c) => c.name && c.credit > 0 && c.grade) &&
        this.priorGPA !== "" &&
        this.priorCredits !== ""
      );
    },
    currentCredits() {
      return this.courses.reduce((total, c) => total + (c.credit || 0), 0);
    },
  },
  methods: {
    addCourse() {
      this.courses.push({ name: "", credit: "", grade: "", description: "" });
    },
    removeCourse(index) {
      this.courses.splice(index, 1);
    },
    calculateCGPA() {
      let totalPoints = 0;
      let totalCredits = 0;

      for (const course of this.courses) {
        if (course.grade && course.credit > 0) {
          totalPoints += this.gradeMap[course.grade] * course.credit;
          totalCredits += course.credit;
        }
      }

      this.sgpa = totalCredits > 0 ? totalPoints / totalCredits : null;

      if (
        this.sgpa !== null &&
        this.priorGPA !== "" &&
        this.priorCredits >= 0
      ) {
        const totalEarnedCredits = this.priorCredits + totalCredits;
        const cumulativePoints =
          this.priorGPA * this.priorCredits + this.sgpa * totalCredits;
        this.cgpa =
          totalEarnedCredits > 0 ? cumulativePoints / totalEarnedCredits : null;
      }
    },
  },
};
</script>

<style scoped>
.cgpa-tool {
  max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
