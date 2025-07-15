<template>
  <AppLayout :role="'Admin'" :navItems="navItems" :pageTitle="pageTitle">
    <div class="container py-4">
      <h2 class="mb-4 fw-bold">Welcome, Admin</h2>

      <div class="row g-4">
        <!-- Manage Users -->
        <div class="col-md-6 col-lg-3">
          <router-link to="/admin/users" class="card card-link h-100 bg-primary text-white">
            <div class="card-body">
              <h5 class="card-title">Manage User Accounts</h5>
              <p class="card-text small">Create, edit, or remove users and assign roles.</p>
            </div>
          </router-link>
        </div>

        <!-- Assign Lecturers -->
        <div class="col-md-6 col-lg-3">
          <router-link to="/admin/assign-lecturers" class="card card-link h-100 bg-success text-white">
            <div class="card-body">
              <h5 class="card-title">Assign Lecturers</h5>
              <p class="card-text small">Map lecturers to their assigned courses.</p>
            </div>
          </router-link>
        </div>

        <!-- View Logs -->
        <div class="col-md-6 col-lg-3">
          <router-link to="/admin/logs" class="card card-link h-100 bg-warning text-dark">
            <div class="card-body">
              <h5 class="card-title">System Logs</h5>
              <p class="card-text small">Review login activity and mark update logs.</p>
            </div>
          </router-link>
        </div>

        <!-- Reset Passwords -->
        <div class="col-md-6 col-lg-3">
          <router-link to="/admin/reset" class="card card-link h-100 bg-danger text-white">
            <div class="card-body">
              <h5 class="card-title">Reset Passwords</h5>
              <p class="card-text small">Securely reset user login credentials.</p>
            </div>
          </router-link>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="mt-5">
        <h4 class="fw-semibold mb-3">Activity Overview</h4>
        <canvas id="adminChart" ref="adminChart" height="100"></canvas>
        <div v-if="loading" class="text-muted mt-2">Loading chart data...</div>
      </div>
    </div>
  </AppLayout>
</template>

<script>
import AppLayout from '@/layouts/AppLayout.vue'
import navItems from '@/constants/adminNavItems'
import Chart from 'chart.js/auto'
import axios from 'axios'

export default {
  name: 'AdminDashboard',
  components: { AppLayout },
  data() {
    return {
      navItems,
      pageTitle: 'Dashboard',
      loading: true,
      chart: null
    }
  },
  mounted() {
    this.loadChartData()
  },
  methods: {
    loadChartData() {
      this.loading = true
      axios.get('http://localhost:8080/api/admin/dashboard-stats')
        .then(res => {
          const stats = res.data
          const ctx = this.$refs.adminChart

          if (this.chart) this.chart.destroy()

          this.chart = new Chart(ctx, {
            type: 'bar',
            data: {
              labels: ['Users', 'Courses', 'Log Entries', 'Resets'],
              datasets: [{
                label: 'Total Count',
                data: [stats.users, stats.courses, stats.logins, stats.resets],
                backgroundColor: ['#0d6efd', '#198754', '#ffc107', '#dc3545']
              }]
            },
            options: {
              responsive: true,
              plugins: {
                legend: { display: false }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  ticks: {
                    stepSize: 1
                  }
                }
              }
            }
          })
        })
        .catch(err => {
          console.error('Failed to load dashboard stats:', err)
        })
        .finally(() => {
          this.loading = false
        })
    }
  }
}
</script>

<style scoped>
.card-link {
  text-decoration: none;
  border-radius: 1rem;
  transition: transform 0.2s ease;
}
.card-link:hover {
  transform: scale(1.03);
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}
</style>
