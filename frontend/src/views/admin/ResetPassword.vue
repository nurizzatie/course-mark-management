<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Reset Password</h2>

      <div class="card">
        <div class="card-body">
          <!-- ✅ Alert Message -->
          <div v-if="alert.show" class="alert" :class="'alert-' + alert.type" role="alert">
            {{ alert.message }}
          </div>

          <!-- ✅ Reset Form -->
          <form @submit.prevent="handleReset">
            <div class="mb-3">
              <label for="matric" class="form-label">Matric Number</label>
              <input
                v-model="form.matric_number"
                id="matric"
                class="form-control"
                placeholder="Enter user's matric number"
                required
              />
            </div>

            <div class="mb-3">
              <label for="newPassword" class="form-label">New Password</label>
              <input
                type="password"
                v-model="form.password"
                id="newPassword"
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
import axios from 'axios'
import AppLayout from '@/layouts/AppLayout.vue'
import adminNavItems from '@/constants/adminNavItems'

export default {
  name: 'ResetPassword',
  components: { AppLayout },
  data() {
    return {
      navItems: adminNavItems,
      pageTitle: 'Reset Password',
      form: {
        matric_number: '',
        password: ''
      },
      alert: {
        show: false,
        type: '',
        message: ''
      }
    }
  },
  methods: {
    showAlert(type, message) {
      this.alert = { show: true, type, message }
      setTimeout(() => (this.alert.show = false), 3000)
    },
    handleReset() {
      axios
        .put('http://localhost:8080/api/admin/reset-password', this.form)
        .then(() => {
          this.showAlert('success', '✅ Password reset successfully')
          this.form.matric_number = ''
          this.form.password = ''
        })
        .catch(() => {
          this.showAlert('danger', '❌ Failed to reset password')
        })
    }
  }
}
</script>
