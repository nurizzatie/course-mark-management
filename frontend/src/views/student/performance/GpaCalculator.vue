<template>
  <div class="gpa-tool">
    <h2 class="mb-3">GPA Calculator</h2>

    <form @submit.prevent="calculateGPA">
      <div
        v-for="(course, index) in courses"
        :key="index"
        class="course-input d-flex align-items-center gap-2 mb-2"
      >
        <input
          v-model="course.name"
          placeholder="Course Name"
          class="form-control"
        />
        <input
          v-model.number="course.credit"
          type="number"
          placeholder="Credit Hours"
          class="form-control"
          min="1"
          @keydown="blockNonNumbers"
        />
        <select v-model="course.grade" class="form-select">
          <option disabled value="">Grade</option>
          <option v-for="(value, grade) in gradeMap" :key="grade" :value="grade">
            {{ grade }}
          </option>
        </select>
        <button
          type="button"
          class="btn btn-sm btn-outline-danger"
          @click="removeCourse(index)"
        >
          ✕
        </button>
      </div>

      <div class="d-flex gap-2 my-3">
        <button type="button" class="btn btn-secondary" @click="addCourse">
          + Add Course
        </button>
        <button type="submit" class="btn btn-dark" :disabled="!isFormValid">
          Calculate GPA
        </button>
      </div>
    </form>

    <!-- 🎯 GPA Result -->
    <div v-if="gpa !== null" class="alert alert-success mt-3">
      Your GPA is: <strong>{{ gpa.toFixed(2) }}</strong>
    </div>

    <!-- Grade Table -->
    <hr class="my-4" />
    <h5> Grade Conversion Table</h5>
    <table class="table table-striped">
      <thead class="table-dark">
        <tr>
          <th>Grade</th>
          <th>Marks</th>
          <th>Grade Points</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>A+</td><td>90-100</td><td>4.00</td></tr>
        <tr><td>A</td><td>80-89</td><td>4.00</td></tr>
        <tr><td>A-</td><td>75-79</td><td>3.67</td></tr>
        <tr><td>B+</td><td>70-74</td><td>3.33</td></tr>
        <tr><td>B</td><td>65-69</td><td>3.00</td></tr>
        <tr><td>B-</td><td>60-64</td><td>2.67</td></tr>
        <tr><td>C+</td><td>55-59</td><td>2.33</td></tr>
        <tr><td>C</td><td>50-54</td><td>2.00</td></tr>
        <tr><td>C-</td><td>45-49</td><td>1.67</td></tr>
        <tr><td>D+</td><td>40-44</td><td>1.33</td></tr>
        <tr><td>D</td><td>35-39</td><td>1.00</td></tr>
        <tr><td>D-</td><td>30-34</td><td>0.67</td></tr>
        <tr><td>E</td><td>0-29</td><td>0.00</td></tr>
      </tbody>
    </table>
  </div>
</template>

<script>
export default {
  name: 'GpaCalculator',
  data() {
    return {
      courses: [{ name: '', credit: '', grade: '' }],
      gpa: null,
      gradeMap: {
        'A+': 4.0, 'A': 4.0, 'A-': 3.67,
        'B+': 3.33, 'B': 3.0, 'B-': 2.67,
        'C+': 2.33, 'C': 2.0, 'C-': 1.67,
        'D+': 1.33, 'D': 1.0, 'D-': 0.67,
        'E': 0.0
      }
    };
  },
  computed: {
    isFormValid() {
      return this.courses.some(c => c.name && c.credit > 0 && c.grade);
    }
  },
  methods: {
    addCourse() {
      this.courses.push({ name: '', credit: '', grade: '' });
    },
    removeCourse(index) {
      this.courses.splice(index, 1);
    },
    blockNonNumbers(event) {
      const allowedKeys = ['Backspace', 'ArrowLeft', 'ArrowRight', 'Tab', 'Delete'];
      if (
        event.key.length === 1 &&
        !/^[0-9]$/.test(event.key) &&
        !allowedKeys.includes(event.key)
      ) {
        event.preventDefault();
      }
    },
    async calculateGPA() {
      // Frontend GPA Calculation
      let totalPoints = 0;
      let totalCredits = 0;

      for (const course of this.courses) {
        if (course.grade && course.credit > 0) {
          totalPoints += this.gradeMap[course.grade] * course.credit;
          totalCredits += course.credit;
        }
      }

      this.gpa = totalCredits > 0 ? totalPoints / totalCredits : null;

      // Optional API Call (kalau nak simpan GPA ke server)
      try {
        const res = await fetch('http://localhost:8080/api/calculate-gpa', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ courses: this.courses })
        });

        const result = await res.json();
        console.log('API GPA Response:', result);

        // Optional override
        if (result.gpa !== undefined) {
          this.gpa = result.gpa;
        }
      } catch (error) {
        console.error('API error:', error);
      }
    },
    async fetchCourses(studentId) {
      try {
        const res = await fetch(`http://localhost:8080/api/student/${studentId}/courses`);
        const data = await res.json();
        this.courses = data.map(course => ({
          name: `${course.course_code} - ${course.course_name}`,
          credit: course.credit_hour,
          grade: ''
        }));
      } catch (err) {
        console.error('Error fetching courses:', err);
      }
    }
  },
  mounted() {
    const user = JSON.parse(localStorage.getItem('user'));
    if (user?.id) {
      this.fetchCourses(user.id);
    }
  }
};
</script>

<style scoped>
.gpa-tool {
  max-width: 100%;
  margin: auto;
  padding: 2rem;
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
