<template>
  <div v-if="showModal" class="modal-overlay">
    <div class="modal">
      <h3>🔒 Set New Password</h3>
      <input
        type="password"
        v-model="newPassword"
        placeholder="New Password"
        class="input"
      />
      <input
        type="password"
        v-model="confirmPassword"
        placeholder="Confirm Password"
        class="input"
      />
      <div class="actions">
        <button @click="updatePassword" class="btn">Reset Password</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'ForceReset',
  props: ['user'],
  data() {
    return {
      newPassword: '',
      confirmPassword: '',
      showModal: true
    };
  },
  methods: {
    async updatePassword() {
      if (!this.newPassword || !this.confirmPassword) {
        alert("Please fill in both fields.");
        return;
      }

      if (this.newPassword !== this.confirmPassword) {
        alert("Passwords do not match.");
        return;
      }

      try {
        await axios.post('http://localhost:8080/api/update-password', {
          id: this.user.id,
          password: this.newPassword
        });

        alert("✅ Password updated successfully.");
        this.showModal = false;

        // Optional: Clear session or user data
        localStorage.removeItem('user');

        this.$router.push('/login');
      } catch (error) {
        console.error(error);
        alert("❌ Failed to update password.");
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 999;
}

.modal {
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.25);
  width: 320px;
  text-align: center;
}

.input {
  width: 100%;
  padding: 10px;
  margin-top: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
}

.actions {
  margin-top: 1.5rem;
}

.btn {
  padding: 10px 20px;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
}

.btn:hover {
  background-color: #2563eb;
}
</style>
