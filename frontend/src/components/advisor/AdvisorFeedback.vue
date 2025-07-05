<template>
  <div class="modal-backdrop">
    <div class="modal-content p-4 bg-white shadow-lg rounded">
      <h5>Feedback for {{ student.name }}</h5>
      <textarea v-model="feedback" class="form-control" rows="4" placeholder="Enter remarks"></textarea>
      <div class="mt-3 d-flex justify-content-end">
        <button class="btn btn-secondary me-2" @click="$emit('close')">Cancel</button>
        <button class="btn btn-primary" @click="submitFeedback">Submit</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  props: ['student'],
  data() {
    return {
      feedback: ''
    }
  },
  methods: {
    submitFeedback() {
  axios.post('http://localhost:8080/api/advisor/feedback', {
    advisor_id: 1, // Replace this with dynamic advisor_id later if needed
    student_id: this.student.id,
    meeting_date: new Date().toISOString().slice(0, 10), // e.g., "2025-07-03"
    note: this.feedback
  })
  .then(() => {
    alert('Feedback submitted successfully');
    this.$emit('close');
  })
  .catch(err => {
    console.error('Failed to submit feedback:', err);
  });
}

  }
}
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.4);
  display: flex;
  justify-content: center;
  align-items: center;
}
.modal-content {
  width: 400px;
}
</style>
