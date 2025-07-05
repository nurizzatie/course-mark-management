<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Reset Password</h2>

      <div class="card">
        <div class="card-body">
          <form @submit.prevent="handleReset">
            <div class="mb-3">
              <label for="email" class="form-label">User Email</label>
              <input
                type="email"
                id="email"
                v-model="email"
                class="form-control"
                placeholder="Enter user's email"
                required
              />
            </div>

            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input
                type="password"
                id="newPassword"
                v-model="newPassword"
                class="form-control"
                placeholder="Enter new password"
                required
              />
            </div>

            <button type="submit" class="btn btn-danger">Reset Password</button>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue';
import adminNavItems from '@/constants/adminNavItems';

export default {
  name: 'ResetPassword',
  components: { AppLayout },
  data() {
    return {
      email: '',
      newPassword: '',
      navItems: adminNavItems,
      pageTitle: 'Reset Password'
    };
  },
  methods: {
    handleReset() {
      // Replace with real API call
      console.log('Resetting password for:', this.email, 'to:', this.newPassword);
      alert('Password reset successfully (mock)');
      this.email = '';
      this.newPassword = '';
    }
  }
};
</script>
