<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Reset Password</h2>

      <div class="card">
        <div class="card-body">
          <!-- ✅ Alert Message -->
          <div
            v-if="alert.show"
            class="alert"
            :class="'alert-' + alert.type"
            role="alert"
          >
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

          <!-- ✅ Divider -->
          <hr class="my-4" />

          <!-- ✅ Pending Reset Requests -->
          <h5>Pending Reset Requests</h5>
          <div v-if="pendingRequests.length === 0" class="text-muted">
            No pending requests.
          </div>

          <ul class="list-group mt-2" v-else>
            <li
              class="list-group-item d-flex justify-content-between align-items-center"
              v-for="req in pendingRequests"
              :key="req.id"
            >
              <div>
                <strong>{{ req.matric_number }}</strong> — {{ req.email }}
              </div>
              <button
                class="btn btn-sm btn-outline-primary"
                @click="form.matric_number = req.matric_number"
              >
                Autofill
              </button>
            </li>
          </ul>
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
      },
      pendingRequests: [] // 🔥 Add this
    }
  },
  methods: {
    showAlert(type, message) {
      this.alert = { show: true, type, message }
      setTimeout(() => (this.alert.show = false), 3000)
    },
   handleReset() {
  axios
    .put(`${process.env.VUE_APP_API_URL}/api/admin/reset-password`, this.form)
    .then(() => {
      // ✅ Now mark the reset request as done
      return axios.put(`${process.env.VUE_APP_API_URL}/api/reset-done`,
        { matric_number: this.form.matric_number },
        { headers: { 'Content-Type': 'application/json' } } // make sure it's JSON
      );
    })
    .then(() => {
      this.showAlert('success', 'Password reset successfully');
      this.form.matric_number = '';
      this.form.password = '';
      this.fetchPendingRequests(); // refresh the list
    })
    .catch((error) => {
      if (error.response?.data?.error === 'Matric number required') {
        this.showAlert('danger', 'Matric number missing');
      } else {
        this.showAlert('danger', 'Something went wrong during password reset');
      }
      console.error('Reset error:', error);
    });
   },  

    
    fetchPendingRequests() {
      axios
        .get(`${process.env.VUE_APP_API_URL}/api/reset-requests`)
        .then(res => {
          this.pendingRequests = res.data
        })
        .catch(err => {
          console.error('❌ Failed to fetch reset requests:', err)
        })
    }
  },
  mounted() {
    this.fetchPendingRequests()
  }
}
</script>
 